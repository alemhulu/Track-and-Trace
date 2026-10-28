// Offline field app: works from a downloaded data pack and queues receipts and book issues
// until a connection is available. Everything is kept in this browser's localStorage.
window.fieldApp = function (config) {
    const key = (name) => 'tt-field-' + name + '-' + config.userId;

    const load = (name, fallback) => {
        try {
            const raw = window.localStorage.getItem(key(name));
            return raw ? JSON.parse(raw) : fallback;
        } catch (e) {
            return fallback;
        }
    };

    const uuid = () => {
        if (window.crypto && window.crypto.randomUUID) {
            return window.crypto.randomUUID();
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    };

    // Same matching rules as the server: case and spacing do not matter
    const normalizeCode = (text) => String(text || '').replace(/\s+/g, ' ').trim().toLowerCase();
    const zoneKey = (name) => String(name || '').toLowerCase()
        .replace(/[^\p{L}\p{N}]+/gu, ' ').trim()
        .replace(/\s+(zone|sub ?city|subcity)$/, '').trim();

    return {
        online: navigator.onLine,
        pack: load('pack', null),
        queue: load('queue', []),
        log: load('log', []),
        organizationId: '',
        tab: 'receive',
        busy: false,
        notice: '',
        noticeType: 'info',

        // Receive
        shipmentId: null,
        counts: {},
        receiptNotes: '',
        packageScans: { own: 0, other: [] },

        // Issue
        gradeId: '',
        section: '',
        search: '',
        studentId: null,
        picked: {},
        condition: 'new',
        returnCondition: 'good',

        init() {
            this.organizationId = this.pack ? String(this.pack.organization.id) : (config.defaultOrganization || '');
            window.addEventListener('online', () => { this.online = true; this.sync(); });
            window.addEventListener('offline', () => { this.online = false; });
            window.addEventListener('qr-scanned', (event) => this.scanned(event.detail.text));
            if (this.pack && !this.pack.organization.is_school) {
                this.tab = 'receive';
            }
            if (this.online && this.queue.length) {
                this.sync();
            }
        },

        save() {
            try {
                window.localStorage.setItem(key('pack'), JSON.stringify(this.pack));
                window.localStorage.setItem(key('queue'), JSON.stringify(this.queue));
                window.localStorage.setItem(key('log'), JSON.stringify(this.log.slice(0, 100)));
            } catch (e) {
                this.say('This phone could not store the data. Free some space and try again.', 'error');
            }
        },

        say(message, type = 'info') {
            this.notice = message;
            this.noticeType = type;
            const feedback = new CustomEvent('scan-feedback', { detail: { ok: type !== 'error' } });
            if (type !== 'info') {
                window.dispatchEvent(feedback);
            }
        },

        csrf() {
            return (this.pack && this.pack.csrf) || config.csrf;
        },

        // ---- Download and sync -------------------------------------------------------

        async download() {
            if (!this.organizationId) {
                return this.say('Choose the organization you work for.', 'error');
            }
            if (this.queue.length) {
                return this.say('Sync your saved work before downloading again.', 'error');
            }
            this.busy = true;
            try {
                const response = await fetch(config.packUrl + '?organization=' + encodeURIComponent(this.organizationId), {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (response.status === 401 || response.redirected) {
                    return this.say('Your session has ended. Sign in again, then download.', 'error');
                }
                const body = await response.json();
                if (!response.ok) {
                    return this.say(body.message || 'The data could not be downloaded.', 'error');
                }
                this.pack = body;
                this.resetForms();
                this.tab = body.organization.is_school ? 'issue' : 'receive';
                this.save();
                this.say('Ready to work offline for ' + body.organization.name + '.', 'success');
            } catch (e) {
                this.say('No connection. Try again when online.', 'error');
            } finally {
                this.busy = false;
            }
        },

        async sync() {
            if (!this.queue.length || this.busy) {
                return;
            }
            this.busy = true;
            try {
                const response = await fetch(config.syncUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ actions: this.queue }),
                });
                if (response.status === 401 || response.status === 419 || response.redirected) {
                    return this.say('Sign in again to sync. Your saved work is kept on this phone.', 'error');
                }
                if (!response.ok) {
                    return this.say('The server could not take the sync right now. Your work is kept; try again later.', 'error');
                }
                const body = await response.json();
                if (this.pack && body.csrf) {
                    this.pack.csrf = body.csrf;
                }
                const results = Object.fromEntries(body.results.map((result) => [result.uuid, result]));
                const done = this.queue.filter((action) => results[action.uuid]);
                this.queue = this.queue.filter((action) => !results[action.uuid]);
                this.log = done.map((action) => ({ ...action, ...results[action.uuid] })).concat(this.log);
                const rejected = done.filter((action) => results[action.uuid].status !== 'applied').length;
                this.save();
                this.say(rejected
                    ? rejected + ' of ' + done.length + ' could not be applied. See the Outbox.'
                    : 'Synced ' + done.length + ' ' + (done.length === 1 ? 'action' : 'actions') + '.', rejected ? 'error' : 'success');

                // Fresh numbers once nothing local is waiting
                if (!this.queue.length && this.pack) {
                    this.organizationId = String(this.pack.organization.id);
                    this.busy = false;
                    await this.download();
                }
            } catch (e) {
                this.say('No connection. Your work is kept and will sync when online.', 'error');
            } finally {
                this.busy = false;
            }
        },

        enqueue(type, data, description) {
            this.queue.push({
                uuid: uuid(),
                type: type,
                organization_id: this.pack.organization.id,
                recorded_at: new Date().toISOString(),
                description: description,
                data: data,
            });
            this.save();
            if (this.online) {
                this.sync();
            }
        },

        discardLog() {
            this.log = [];
            this.save();
        },

        resetForms() {
            this.shipmentId = null;
            this.studentId = null;
            this.picked = {};
            this.gradeId = '';
            this.section = '';
        },

        // ---- Scanning ----------------------------------------------------------------

        titleForCode(text) {
            const wanted = normalizeCode(text);
            for (const title of this.pack.titles) {
                const code = title.codes.find((c) => c.text === wanted || (c.print_code && c.print_code === String(text).trim()));
                if (code) {
                    return { title: title, edition: code.edition };
                }
            }
            return null;
        },

        scanned(text) {
            if (!this.pack) {
                return;
            }
            if (this.tab === 'receive' && this.shipmentId) {
                return this.scanPackage(text);
            }
            if (this.tab === 'issue' && this.studentId) {
                return this.scanBook(text);
            }
            this.say(this.tab === 'issue' ? 'Choose a student first.' : 'Open a shipment first.', 'error');
        },

        // Count packages coming off the truck and check each label's zone
        scanPackage(text) {
            const scannedKey = zoneKey(text);
            const zone = this.pack.zones.find((z) => z.key === scannedKey);
            if (!zone) {
                return this.say('"' + text + '" is not a zone label.', 'error');
            }
            const own = this.pack.organization.zone_key;
            const region = this.pack.organization.region_id;
            const belongs = own ? scannedKey === own : (!region || this.pack.zones.some((z) => z.key === scannedKey && z.region_id === region));
            if (belongs) {
                this.packageScans.own++;
                this.say('Package ' + (this.packageScans.own) + ' counted.', 'success');
            } else {
                this.packageScans.other.push(zone.name);
                this.say('Package for ' + zone.name + ' does not belong here. Set it aside and note it below.', 'error');
                this.receiptNotes = (this.receiptNotes ? this.receiptNotes + ' ' : '') + 'Package labelled ' + zone.name + ' received.';
            }
        },

        scanBook(text) {
            const match = this.titleForCode(text);
            if (!match) {
                return this.say('"' + text + '" is not a known book code for this region.', 'error');
            }
            if (match.edition !== 'student') {
                return this.say('That is a teacher guide. Teacher guides are issued online.', 'error');
            }
            const student = this.student();
            if (match.title.grade_id && match.title.grade_id !== student.grade_id) {
                return this.say(match.title.title + ' is not for Grade ' + student.grade + '.', 'error');
            }
            if (student.held.includes(match.title.id)) {
                return this.say(student.name + ' already holds ' + match.title.title + '.', 'error');
            }
            this.picked[match.title.id] = true;
            this.say('Ticked ' + match.title.title + '.', 'success');
        },

        // ---- Receive -----------------------------------------------------------------

        shipments() {
            return this.pack ? this.pack.shipments : [];
        },

        shipment() {
            return this.shipments().find((s) => s.id === this.shipmentId) || null;
        },

        isQueued(shipmentId) {
            return this.queue.some((a) => a.type === 'receive_shipment' && a.data.shipment_id === shipmentId);
        },

        openShipment(id) {
            this.shipmentId = id;
            this.receiptNotes = '';
            this.packageScans = { own: 0, other: [] };
            this.counts = {};
            for (const line of this.shipment().lines) {
                this.counts[line.id] = { student_books: line.student_books, teacher_guides: line.teacher_guides, damaged_quantity: 0, damaged_teacher_guides: 0 };
            }
        },

        expectedPackages() {
            return this.shipment() ? this.shipment().lines.reduce((sum, line) => sum + line.packages, 0) : 0;
        },

        saveReceipt() {
            const shipment = this.shipment();
            for (const line of shipment.lines) {
                const count = this.counts[line.id];
                for (const field of Object.keys(count)) {
                    count[field] = Math.max(0, parseInt(count[field], 10) || 0);
                }
                if (count.damaged_quantity > count.student_books || count.damaged_teacher_guides > count.teacher_guides) {
                    return this.say('Damaged copies of ' + line.title + ' cannot be more than received.', 'error');
                }
            }
            this.enqueue('receive_shipment', {
                shipment_id: shipment.id,
                counts: JSON.parse(JSON.stringify(this.counts)),
                notes: this.receiptNotes,
            }, 'Receipt of ' + shipment.reference + ' from ' + shipment.from);
            this.shipmentId = null;
            this.say('Receipt saved. It will be sent when online.', 'success');
        },

        // ---- Issue -------------------------------------------------------------------

        grades() {
            const seen = {};
            return (this.pack ? this.pack.students : []).filter((s) => !seen[s.grade_id] && (seen[s.grade_id] = true))
                .map((s) => ({ id: s.grade_id, name: s.grade }));
        },

        sections() {
            const list = this.pack.students.filter((s) => String(s.grade_id) === String(this.gradeId) && s.section).map((s) => s.section);
            return [...new Set(list)].sort();
        },

        classList() {
            const search = this.search.trim().toLowerCase();
            return this.pack.students.filter((s) => String(s.grade_id) === String(this.gradeId)
                && (!this.section || s.section === this.section)
                && (!search || s.name.toLowerCase().includes(search) || s.student_no.toLowerCase().includes(search)));
        },

        student() {
            return this.pack.students.find((s) => s.id === this.studentId) || null;
        },

        titlesFor(student) {
            return this.pack.titles.filter((t) => !t.grade_id || t.grade_id === student.grade_id);
        },

        onHand(titleId) {
            return this.pack.stock[titleId] || 0;
        },

        missing(student) {
            return this.titlesFor(student).filter((t) => !student.held.includes(t.id)).length;
        },

        chooseStudent(id) {
            this.studentId = id;
            this.picked = {};
        },

        nextStudent() {
            const list = this.classList();
            const at = list.findIndex((s) => s.id === this.studentId);
            this.chooseStudent(list[at + 1] ? list[at + 1].id : null);
        },

        issuePicked() {
            const student = this.student();
            const ids = Object.keys(this.picked).filter((id) => this.picked[id]).map(Number);
            if (!ids.length) {
                return this.say('Tick or scan the books being handed over.', 'error');
            }
            const short = ids.filter((id) => this.onHand(id) < 1);
            if (short.length) {
                return this.say('Not enough copies on hand of ' + this.pack.titles.find((t) => t.id === short[0]).title + '.', 'error');
            }
            ids.forEach((id) => { student.held.push(id); this.pack.stock[id] = this.onHand(id) - 1; });
            this.enqueue('issue_books', { student_id: student.id, title_ids: ids, condition: this.condition },
                ids.length + (ids.length === 1 ? ' book' : ' books') + ' to ' + student.name);
            this.picked = {};
            this.say('Saved: ' + ids.length + (ids.length === 1 ? ' book' : ' books') + ' to ' + student.name + '.', 'success');
        },

        returnBook(titleId) {
            const student = this.student();
            student.held = student.held.filter((id) => id !== titleId);
            if (!['lost', 'damaged'].includes(this.returnCondition)) {
                this.pack.stock[titleId] = this.onHand(titleId) + 1;
            }
            const title = this.pack.titles.find((t) => t.id === titleId);
            this.enqueue('return_book', { student_id: student.id, title_id: titleId, condition: this.returnCondition },
                'Return of ' + title.title + ' from ' + student.name + ' (' + this.returnCondition + ')');
            this.say('Return saved.', 'success');
        },
    };
};

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}
