@extends('front.layout')

@section('carousel')
    <div class="header-carousel owl-carousel">
        <div class="header-carousel-item">
            <img src="{{ asset('front/img/carousel-1.jpg') }}" class="img-fluid w-100" alt="Image">
            <div class="carousel-caption">
                <div class="carousel-caption-content p-3">
                    <h5 class="text-white text-uppercase fw-bold mb-4" style="letter-spacing: 3px;">Eye Care Center</h5>
                    <h1 class="display-1 text-capitalize text-white mb-4">Best Solution For Painful Life</h1>
                    <p class="mb-5 fs-5">Vision Aid is an AI-powered eye care assistant revolutionizing diabetic retinopathy
                        detection with intelligent diagnostics, personalized recommendations, and accessible eye health
                        monitoring—empowering every patient to see a clearer future.
                    </p>
                    <a class="btn btn-primary rounded-pill text-white py-3 px-5" href="#appointment">Book Appointment</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <!-- Feature Start -->
    <div class="container-fluid feature py-5">
        <div class="container py-5">
            <div class="section-title mb-5 wow fadeInUp" data-wow-delay="0.1s">
                <div class="sub-style">
                    <h4 class="sub-title px-3 mb-0">Try-On</h4>
                </div>
                <h1 class="display-3 mb-4">Virtual Try-On Glasses – See Yourself in Any Frame!</h1>
                <p class="mb-0">Try on glasses virtually with our AI-powered feature! Simply upload a photo or use your
                    camera to see how different frames look on you. Adjust styles, colors, and sizes in real-time to find
                    your perfect match—no need to visit a store. Discover your ideal glasses with ease!</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-4 col-xl-3 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="row-cols-1 feature-item p-4">
                        <div class="col-12">
                            <div class="feature-icon mb-4">
                                <div class="p-3 d-inline-flex bg-white rounded">
                                    <i class="fas fa-diagnoses fa-4x text-primary"></i>
                                </div>
                            </div>
                            <div class="feature-content d-flex flex-column">
                                <h5 class="mb-4">Licensed Therapist</h5>
                                <p class="mb-0">Dolor, sit amet consectetur adipisicing elit. Soluta inventore cum
                                    accusamus,</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 text-center wow fadeInUp" data-wow-delay="0.2s">
                    <a href="{{ Auth::user() && Auth::user()->role == 1 ? '/_user' : '' }}/try-on/"
                        class="btn btn-primary rounded-pill text-white py-3 px-5">Try-On</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Feature End -->


    <!-- Book Appointment Start -->
    <div class="container-fluid appointment py-5" id="appointment">
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 wow fadeInLeft" data-wow-delay="0.2">
                    <div class="section-title text-start">
                        <h4 class="sub-title pe-3 mb-0">Empowering Your Vision</h4>
                        <h1 class="display-4 mb-4">AI-Powered Eye Care with Precision and Compassion</h1>
                        <p class="mb-4">
                            Vision Aid combines cutting-edge deep learning technology with thoughtful care to detect
                            diabetic retinopathy at every stage.
                            Our intelligent system delivers accurate diagnostics, personalized recommendations, and
                            continuous eye health monitoring—making
                            quality care accessible and proactive for all.
                        </p>
                        <div class="row g-4">
                            <div class="col-sm-6">
                                <div class="d-flex flex-column h-100">
                                    <div class="mb-4">
                                        <h5 class="mb-3"><i class="fa fa-check text-primary me-2"></i> Early Detection &
                                            Diagnosis</h5>
                                        <p class="mb-0">Upload retina scans or answer guided questions to receive fast,
                                            accurate diabetic retinopathy predictions powered by AI.</p>
                                    </div>
                                    <div class="mb-4">
                                        <h5 class="mb-3"><i class="fa fa-check text-primary me-2"></i> Personalized
                                            Treatment Guidance</h5>
                                        <p class="mb-0">Receive tailored health insights and treatment roadmaps based on
                                            your eye condition, history, and risk profile.</p>
                                    </div>
                                    <div class="text-start mb-4">
                                        <a href="#" class="btn btn-primary rounded-pill text-white py-3 px-5">Explore
                                            More</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="video h-100">
                                    <img src="{{ asset('front/img/video-img.jpg') }}" class="img-fluid rounded w-100 h-100"
                                        style="object-fit: cover;" alt="Vision Aid Demo">
                                    <button type="button" class="btn btn-play" data-bs-toggle="modal"
                                        data-src="https://www.youtube.com/embed/DWRcNpR6Kdc" data-bs-target="#videoModal">
                                        <span></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.4s">
                    <div class="appointment-form rounded p-5">
                        <p class="fs-4 text-uppercase text-primary">Get In Touch</p>
                        <h1 class="display-5 mb-4">Get Appointment</h1>
                        <form method="POST">
                            @csrf
                            <div class="row gy-3 gx-4">
                                <div class="col-xl-6">
                                    <input type="text" class="form-control py-3 border-primary bg-transparent text-white"
                                        placeholder="First Name">
                                </div>
                                <div class="col-xl-6">
                                    <input type="email" class="form-control py-3 border-primary bg-transparent text-white"
                                        placeholder="Email">
                                </div>
                                <div class="col-xl-6">
                                    <input type="phone" class="form-control py-3 border-primary bg-transparent"
                                        placeholder="Phone">
                                </div>
                                <div class="col-xl-6">
                                    <select class="form-select py-3 border-primary bg-transparent"
                                        aria-label="Default select example">
                                        <option selected>Your Gender</option>
                                        <option value="1">Male</option>
                                        <option value="2">FeMale</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <input type="date" class="form-control py-3 border-primary bg-transparent">
                                </div>
                                <div class="col-12">
                                    <textarea class="form-control border-primary bg-transparent text-white" name="text" id="area-text" cols="30"
                                        rows="5" placeholder="Write Comments"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary text-white w-100 py-3 px-5">SUBMIT
                                        NOW</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Video -->
    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Youtube Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- 16:9 aspect ratio -->
                    <div class="ratio ratio-16x9">
                        <iframe class="embed-responsive-item" src="" id="video" allowfullscreen
                            allowscriptaccess="always" allow="autoplay"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Book Appointment End -->


    <!-- Team Start -->
    <div class="container-fluid team py-5">
        <div class="container py-5">
            <div class="section-title mb-5 wow fadeInUp" data-wow-delay="0.1s">
                <div class="sub-style">
                    <h4 class="sub-title px-3 mb-0">Meet our team</h4>
                </div>
                <h1 class="display-3 mb-4">Physiotherapy Services from Professional Therapist</h1>
                <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Quaerat deleniti amet at atque
                    sequi quibusdam cumque itaque repudiandae temporibus, eius nam mollitia voluptas maxime veniam
                    necessitatibus saepe in ab? Repellat!</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item rounded">
                        <div class="team-img rounded-top h-100">
                            <img src="{{ asset('front/img/team-1.jpg') }}" class="img-fluid rounded-top w-100"
                                alt="">
                            <div class="team-icon d-flex justify-content-center">
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-twitter"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-instagram"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-content text-center border border-primary border-top-0 rounded-bottom p-4">
                            <h5>Full Name</h5>
                            <p class="mb-0">Message Physio Therapist</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item rounded">
                        <div class="team-img rounded-top h-100">
                            <img src="{{ asset('front/img/team-2.jpg') }}" class="img-fluid rounded-top w-100"
                                alt="">
                            <div class="team-icon d-flex justify-content-center">
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-twitter"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-instagram"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-content text-center border border-primary border-top-0 rounded-bottom p-4">
                            <h5>Full Name</h5>
                            <p class="mb-0">Rehabilitation Therapist</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="team-item rounded">
                        <div class="team-img rounded-top h-100">
                            <img src="{{ asset('front/img/team-3.jpg') }}" class="img-fluid rounded-top w-100"
                                alt="">
                            <div class="team-icon d-flex justify-content-center">
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-twitter"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-instagram"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-content text-center border border-primary border-top-0 rounded-bottom p-4">
                            <h5>Full Name</h5>
                            <p class="mb-0">Doctor of Physical therapy</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="team-item rounded">
                        <div class="team-img rounded-top h-100">
                            <img src="{{ asset('front/img/team-4.jpg') }}" class="img-fluid rounded-top w-100"
                                alt="">
                            <div class="team-icon d-flex justify-content-center">
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-twitter"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-instagram"></i></a>
                                <a class="btn btn-square btn-primary text-white rounded-circle mx-1" href=""><i
                                        class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="team-content text-center border border-primary border-top-0 rounded-bottom p-4">
                            <h5>Full Name</h5>
                            <p class="mb-0">Doctor of Physical therapy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->
@endsection
