<!DOCTYPE html>
<html lang="en">
    @include('layouts.head')
<body>
	<div class="wrapper">
		<div class="main-header">
			<!-- Logo Header -->
                @include('layouts.logo_header')
			<!-- End Logo Header -->

			<!-- Navbar Header -->
                @include('layouts.navbar')
			<!-- End Navbar -->
		</div>

		<!-- Sidebar -->
            @include('layouts.sidebar')
		<!-- End Sidebar -->

		<div class="main-panel">
			<div class="content">
				{{ $slot }}
			</div>

            <!-- Footer -->
			    @include('layouts.footer')
            <!-- End Footer -->
		</div>
	</div>

    <!-- Core JS Files -->
    <script src="{{ asset('atlantis/assets/js/core/jquery.3.2.1.min.js') }}"></script>
    <script src="{{ asset('atlantis/assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('atlantis/assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('atlantis/assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('atlantis/assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js') }}"></script>
    <script src="{{ asset('atlantis/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('atlantis/assets/js/plugin/chart.js/chart.min.js') }}"></script>
    <script src="{{ asset('atlantis/assets/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('atlantis/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('atlantis/assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('atlantis/assets/js/atlantis.min.js') }}"></script>
    @stack('scripts')
    @livewireScripts
</body>
</html>