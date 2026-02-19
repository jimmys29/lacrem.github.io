(function ($) {
    "use strict";
    
    // Smooth scrolling on the navbar links
    $(".navbar-nav a").on('click', function (event) {
        if (this.hash !== "") {
            event.preventDefault();
            
            $('html, body').animate({
                scrollTop: $(this.hash).offset().top - 45
            }, 1500, 'easeInOutExpo');
            
            if ($(this).parents('.navbar-nav').length) {
                $('.navbar-nav .active').removeClass('active');
                $(this).closest('a').addClass('active');
            }
        }
    });
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Modal Video
    $(document).ready(function () {
        var $videoSrc;
        $('.btn-play').click(function () {
            $videoSrc = $(this).data("src");
        });
        console.log($videoSrc);

        $('#videoModal').on('shown.bs.modal', function (e) {
            $("#video").attr('src', $videoSrc + "?autoplay=1&amp;modestbranding=1&amp;showinfo=0");
        })

        $('#videoModal').on('hide.bs.modal', function (e) {
            $("#video").attr('src', $videoSrc);
        })
    });


    // Service and team carousel (exclude vertical layouts)
    $(".service-carousel:not(.vertical), .team-carousel").owlCarousel({
        autoplay: false,
        smartSpeed: 1500,
        margin: 30,
        dots: false,
        loop: true,
        nav : true,
        navText : [
            '<i class="fa fa-angle-left" aria-hidden="true"></i>',
            '<i class="fa fa-angle-right" aria-hidden="true"></i>'
        ],
        responsive: {
            0:{
                items:1
            },
            576:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:3
            }
        }
    });


    // Product carousel
    $(".product-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        margin: 30,
        dots: false,
        loop: true,
        nav : true,
        navText : [
            '<i class="fa fa-angle-left" aria-hidden="true"></i>',
            '<i class="fa fa-angle-right" aria-hidden="true"></i>'
        ],
        responsive: {
            0:{
                items:1
            },
            576:{
                items:2
            },
            768:{
                items:3
            },
            992:{
                items:4
            }
        }
    });


    // Portfolio isotope and filter
    var portfolioIsotope = $('.portfolio-container').isotope({
        itemSelector: '.portfolio-item',
        layoutMode: 'fitRows'
    });

    $('#portfolio-flters li').on('click', function () {
        $("#portfolio-flters li").removeClass('active');
        $(this).addClass('active');

        portfolioIsotope.isotope({filter: $(this).data('filter')});
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        dots: true,
        loop: true,
        items: 1
    });

    // WhatsApp widget
    $(document).ready(function () {
        if ($('#whatsapp-widget').length) {
            return;
        }

        var whatsappStyles = `
            <style>
                .whatsapp-widget {
                    position: fixed;
                    bottom: 180px;
                    right: 24px;
                    z-index: 10000;
                    font-family: 'Poppins', sans-serif;
                }

                .whatsapp-widget__button {
                    width: 64px;
                    height: 64px;
                    border-radius: 50%;
                    background-color: #25D366;
                    color: #ffffff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 30px;
                    cursor: pointer;
                    box-shadow: 0 6px 18px rgba(37, 211, 102, 0.35);
                    animation: whatsapp-pulse 2.6s ease-in-out infinite;
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }

                .whatsapp-widget__button:hover {
                    transform: scale(1.08);
                    box-shadow: 0 10px 24px rgba(37, 211, 102, 0.45);
                }

                .whatsapp-widget__label {
                    position: absolute;
                    right: 0;
                    bottom: 76px;
                    background-color: #030303;
                    color: #ffffff;
                    padding: 10px 14px;
                    border-radius: 18px;
                    font-size: 13px;
                    font-weight: 600;
                    white-space: nowrap;
                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
                    opacity: 0;
                    transform: translateY(8px);
                    transition: opacity 0.2s ease, transform 0.2s ease;
                    pointer-events: auto;
                }

                .whatsapp-widget__label::after {
                    content: '';
                    position: absolute;
                    right: 16px;
                    bottom: -8px;
                    border-left: 8px solid transparent;
                    border-right: 8px solid transparent;
                    border-top: 8px solid #25D366;
                }

                .whatsapp-widget:hover .whatsapp-widget__label,
                .whatsapp-widget__label:hover {
                    opacity: 1;
                    transform: translateY(0);
                }

                @keyframes whatsapp-pulse {
                    0% {
                        transform: scale(1);
                    }
                    50% {
                        transform: scale(1.04);
                    }
                    100% {
                        transform: scale(1);
                    }
                }

                @media (max-width: 576px) {
                    .whatsapp-widget {
                        bottom: 160px;
                        right: 18px;
                    }

                    .whatsapp-widget__button {
                        width: 56px;
                        height: 56px;
                        font-size: 26px;
                    }

                    .whatsapp-widget__label {
                        bottom: 68px;
                        font-size: 12px;
                    }
                }
            </style>
        `;

        $('head').append(whatsappStyles);

        var whatsappHTML = `
            <div class="whatsapp-widget" id="whatsapp-widget">
                <div class="whatsapp-widget__button" id="whatsapp-widget-button" aria-label="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div class="whatsapp-widget__label" id="whatsapp-widget-label">necesitas ayuda?</div>
            </div>
        `;

        $('body').append(whatsappHTML);

        var whatsappUrl = 'https://wa.me/573174137207';
        $('#whatsapp-widget-button, #whatsapp-widget-label').on('click', function () {
            window.open(whatsappUrl, '_blank');
        });
    });
    
})(jQuery);

