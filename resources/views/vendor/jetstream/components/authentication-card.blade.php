<div class="w-full min-h-screen flex justify-center items-center pt-6 sm:pt-0 bg-no-repeat bg-left bg-cover"
    style="background-image: url('https://flevix.com/wp-content/uploads/2019/12/Live-Background-1.svg')">

    <div class="flex justify-center flex-col md:flex-row h-auto drop-shadow-2xl rounded-2xl shadow-2xl shadow-indigo-200  w-7/12 backdrop-filter backdrop-blur-3xl backdrop-opacity-90 "
        style=" backdrop-filter: blur(14px);">
        <div class="w-full hidden px-6 py-4 md:block bg-gray-100/70  rounded-l-2xl bg-no-repeat bg-cover bg-center"
            style="background-image: url('https://img.freepik.com/free-vector/cloud-computing-security-abstract-concept-illustration_335657-2105.jpg?w=2000">
            <div class="flex items-center justify-center h-full ">
                {{ $logo }}
            </div>
        </div>

        <div class="w-full  px-8 py-10 backdrop-brightness-150 md:rounded-r-2xl rounded-md md:rounded-none"
            style="background-color: #f9f9f9; ">
            <h1 class="flex md:px-8 md:text-2xl text-center font-black mb-8 font-sans text-lime-800">
                Welcome to MoE Books {{ config('app.name', 'Track and Trace') }}
            </h1>
            <div class="pt-15 md:px-8">
                {{ $slot }}
            </div>
        </div>
    </div>

</div>