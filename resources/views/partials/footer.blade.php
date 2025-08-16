<footer class="bg-primary text-white py-5 mt-5 shadow-sm">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-heart-pulse-fill fs-2 me-3"></i>
                    <h4 class="fw-bold mb-0">CliniQ</h4>
                </div>
                <p class="text-white-50 mb-0 lh-base">
                    Your trusted partner in healthcare management.
                    Streamlining clinic operations for better patient care.
                </p>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="fw-semibold mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('welcome') }}" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="bi bi-house me-2"></i>Home
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('clinics.index') }}" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="bi bi-search me-2"></i>Find Clinics
                        </a>
                    </li>
                    @auth
                        <li class="mb-2">
                            <a href="{{ route('dashboard') }}" class="text-white-50 text-decoration-none d-flex align-items-center">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="fw-semibold mb-3">Services</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="bi bi-calendar-check me-2"></i>Appointments
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="bi bi-person-badge me-2"></i>Doctors
                        </a>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="fw-semibold mb-3">Support</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="bi bi-question-circle me-2"></i>Help Center
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="bi bi-envelope me-2"></i>Contact Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="bi bi-shield-check me-2"></i>Privacy Policy
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="my-4 border-white border-opacity-25">

        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-white-50 mb-0">
                    &copy; {{ date('Y') }} CliniQ. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="social-links">
                    <a href="#" class="text-white-50 text-decoration-none me-3 p-2 rounded-circle bg-white bg-opacity-10 d-inline-block"
                       style="width: 40px; height: 40px; line-height: 26px; text-align: center;">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="text-white-50 text-decoration-none me-3 p-2 rounded-circle bg-white bg-opacity-10 d-inline-block"
                       style="width: 40px; height: 40px; line-height: 26px; text-align: center;">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="#" class="text-white-50 text-decoration-none me-3 p-2 rounded-circle bg-white bg-opacity-10 d-inline-block"
                       style="width: 40px; height: 40px; line-height: 26px; text-align: center;">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <a href="#" class="text-white-50 text-decoration-none p-2 rounded-circle bg-white bg-opacity-10 d-inline-block"
                       style="width: 40px; height: 40px; line-height: 26px; text-align: center;">
                        <i class="bi bi-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
