(function () {
	'use strict';

	var tinyslider = function () {
		var el = document.querySelectorAll('.testimonial-slider');

		if (el.length > 0) {
			var slider = tns({
				container: '.testimonial-slider',
				items: 1,
				axis: "horizontal",
				controlsContainer: "#testimonial-nav",
				swipeAngle: false,
				speed: 700,
				nav: true,
				controls: true,
				autoplay: true,
				autoplayHoverPause: true,
				autoplayTimeout: 3500,
				autoplayButtonOutput: false
			});
		}
	};
	tinyslider();




	var sitePlusMinus = function () {

		var value,
			quantity = document.getElementsByClassName('quantity-container');

		function createBindings(quantityContainer) {
			var quantityAmount = quantityContainer.getElementsByClassName('quantity-amount')[0];
			var increase = quantityContainer.getElementsByClassName('increase')[0];
			var decrease = quantityContainer.getElementsByClassName('decrease')[0];
			increase.addEventListener('click', function (e) { increaseValue(e, quantityAmount); });
			decrease.addEventListener('click', function (e) { decreaseValue(e, quantityAmount); });
		}

		function init() {
			for (var i = 0; i < quantity.length; i++) {
				createBindings(quantity[i]);
			}
		};

		function increaseValue(event, quantityAmount) {
			value = parseInt(quantityAmount.value, 10);

			console.log(quantityAmount, quantityAmount.value);

			value = isNaN(value) ? 0 : value;
			value++;
			quantityAmount.value = value;
		}

		function decreaseValue(event, quantityAmount) {
			value = parseInt(quantityAmount.value, 10);

			value = isNaN(value) ? 0 : value;
			if (value > 0) value--;

			quantityAmount.value = value;
		}

		init();

	};
	sitePlusMinus();


})()


document.addEventListener('DOMContentLoaded', function () {
	const carousel = document.querySelector('.custom-news-carousel');
	const track = document.querySelector('.custom-news-track');
	const slides = document.querySelectorAll('.custom-news-slide');
	const prevBtn = document.querySelector('.custom-news-prev');
	const nextBtn = document.querySelector('.custom-news-next');
	const pagination = document.querySelector('.custom-news-pagination');

	let currentSlide = 0;
	const slidesToShow = 3;
	const totalSlides = slides.length;

	// Create pagination dots
	for (let i = 0; i < Math.ceil(totalSlides / slidesToShow); i++) {
		const dot = document.createElement('button');
		dot.className = 'custom-news-dot';
		if (i === 0) dot.classList.add('active');
		dot.addEventListener('click', () => goToSlide(i));
		pagination.appendChild(dot);
	}

	function updateCarousel() {
		const slideWidth = slides[0].offsetWidth + 30; // Include gap
		track.style.transform = `translateX(-${currentSlide * slideWidth}px)`;

		// Update pagination dots
		document.querySelectorAll('.custom-news-dot').forEach((dot, index) => {
			dot.classList.toggle('active', index === currentSlide);
		});
	}

	function goToSlide(slideIndex) {
		currentSlide = slideIndex;
		updateCarousel();
	}

	function nextSlide() {
		currentSlide = (currentSlide + 1) % Math.ceil(totalSlides / slidesToShow);
		updateCarousel();
	}

	function prevSlide() {
		currentSlide = (currentSlide - 1 + Math.ceil(totalSlides / slidesToShow)) % Math.ceil(totalSlides / slidesToShow);
		updateCarousel();
	}

	// Event listeners
	prevBtn.addEventListener('click', prevSlide);
	nextBtn.addEventListener('click', nextSlide);

	// Auto-slide functionality
	let autoSlideInterval = setInterval(nextSlide, 5000);

	// Pause auto-slide on hover
	carousel.addEventListener('mouseenter', () => {
		clearInterval(autoSlideInterval);
	});

	carousel.addEventListener('mouseleave', () => {
		autoSlideInterval = setInterval(nextSlide, 5000);
	});

	// Handle window resize
	window.addEventListener('resize', updateCarousel);
});