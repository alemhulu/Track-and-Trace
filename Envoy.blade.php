@servers(['web' => 'deployer@172.20.0.186'])
@setup
$repository = 'git@git.moe.gov.et:natiztm/book-track-and-trace.git';
$releases_dir = '/var/www/html/releases';
$app_dir = '/var/www/book-track-and-trace';
$release = date('YmdHis');
$new_release_dir = $releases_dir .'/'. $release;
$password = 'dep@1234';//deployer user password
@endsetup
@story('deploy')
clone_repository
run_composer
update_symlinks
@endstory
@task('clone_repository')
echo 'Cloning repository'
[ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
git clone --depth 1 {{ $repository }} {{ $new_release_dir }}
cd {{ $new_release_dir }}
git reset --hard {{ $commit }}
@endtask
@task('run_composer')
echo "Starting deployment ({{ $release }})"
cd {{ $new_release_dir }}
composer install --prefer-dist --no-scripts -q -o
@endtask
@task('update_symlinks')
echo 'Linking current release'
ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current
echo "{{ $password }}" | sudo -S chmod 777 -R {{ $app_dir }}
@endtask