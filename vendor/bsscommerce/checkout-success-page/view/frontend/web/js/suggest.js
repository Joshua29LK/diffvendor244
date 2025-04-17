/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutSuccessPage
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
require(
    [
    'jquery'
    ],
    function ($) {
    
        var slideIndex = 1;
        showSlides(slideIndex);

        function plusSlides(n)
        {
            showSlides(slideIndex += n);
        }

        function showSlides(n)
        {
            var i;
            var slides = document.getElementsByClassName("mySlides");

            if (n > slides.length) {
                slideIndex = 1}
            if (n < 1) {
                slideIndex = slides.length}
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }

              slides[slideIndex-1].style.display = "block";

        }

        $(".prev-suggest").click(function () {
            plusSlides(-1);
        });
        $(".next-suggest").click(function () {
            plusSlides(1);
        });

        function autoSlider()
        {
            var i;
            var slides = document.getElementsByClassName("mySlides");

            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slideIndex++;
            if (slideIndex> slides.length) {
                slideIndex = 1}

            slides[slideIndex-1].style.display = "block";

            setTimeout(autoSlider, 4000);
        }
        autoSlider();
    }
);
