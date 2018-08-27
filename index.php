<?php
  include 'php/data.php';
  $page = 'index';
  $countries = $countries_data;
  $packages = $packages_data;
  $services = $services_data;
  $features = $features_data;
  $pathPrefix = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Globuzzer</title>
    <!------ STYLESHEETS ------->
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700,900|Source+Sans+Pro:400,500,700,900" rel="stylesheet">
    <!-- Simple grid -->
    <link rel="stylesheet" href="css/simple-grid.css">
    <!-- jQuery -->
    <link rel="stylesheet" href="css/jquery-ui.theme.min.css">
    <link rel="stylesheet" href="css/jquery-ui.min.css">
    <!-- Own stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/swiper.css">

    <!------ SCRIPTS ------->
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/jquery-ui.js"></script>
    <!-- Font Awesome -->
    <script defer src="https://use.fontawesome.com/releases/v5.0.7/js/all.js"></script>
    <!-- Swiper JS -->
    <script src="js/swiper.js"></script>
    <!-- lodash -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/2.4.1/lodash.min.js"></script>
    <script>
        // import lodash
        _ = _.noConflict();
        // import data
        var countries = <?php echo json_encode($countries) ?>;
        var packages = <?php echo json_encode($packages) ?>;
        var services = <?php echo json_encode($services) ?>;
        var pathPrefix = '<?php echo $pathPrefix; ?>';
    </script>

    <script type="text/javascript" src="js/script-with-data.js"></script>
    <script>
        // validates email address
        function validateEmail(email) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }

        var wChatOpen = false;

        var swiper = null;
        var swiperIndex = 0;
        var swiperTimeout = null;
        var contactError;
        $(document).ready(function() {

          //toggle mobile menu
          $('#mobilemenu').click(function() {
              $('#mainmenu').slideToggle('slow');
          });

          //Change header-image and text on city change
          _.each(countries, function(country) {
            $('.country-card.' + country.key + '-card').click(function(evt) {
              setCountry(country);
              $('#country-select').val(country.key);
              if (swiperTimeout) {
                clearTimeout(swiperTimeout);
                swiperTimeout = null;
              }
            });
            $('.country-card.' + country.key + '-card .button').click(function(evt) {
              setCountry(country);
              $('#country-select').val(country.key);
              if (swiperTimeout) {
                clearTimeout(swiperTimeout);
                swiperTimeout = null;
              }
            });
          });
          $('#country-select').change(function() {
            var currCountry = $(this).val();
            setCountry(countries[currCountry]);
            location.href = '#' + currCountry;
            if (swiperTimeout) {
              clearTimeout(swiperTimeout);
              swiperTimeout = null;
            }
          });

          $('#people').change(function() {
            localStorage.people = parseInt($('#people').val()) || 0;
          });
          if (localStorage.people && !isNaN(localStorage.people)) {
            $('#people').val(localStorage.people);
          } else {
            $('#people').val('');
          }

          // Shows the number of chosen services in the package
          var clickcount = 0;
          for (var i = 0; i < localStorage.length; i++) {
              var key = localStorage.key(i);
              if (services[key]) {
                clickcount++;
              }
          }
          localStorage.clickcount = clickcount;
          if (localStorage.clickcount && localStorage.clickcount != 0) {
              $("#num-packages").html(localStorage.clickcount);
          } else {
              $("#num-packages").hide();
          }

          var currCountry = countries['sweden'];
          var countryIndex = 0;
          if (location.hash.indexOf('#') != -1) {
            var hash = location.hash.substr(1);
            _.each(countries, function(country) {
              if (hash.startsWith(country.key)) {
                currCountry = country;
                $('#country-select').val(country.key);
                return false;
              }
              countryIndex++;
            });
          } else {
            var swipe = function() {
              swiper.slideNext(800);
              wChangingSlide = true;
              transitionEnd();
              swiperTimeout = setTimeout(swipe, 6000);
            }
            swiperTimeout = setTimeout(swipe, 6000);
          }
          var wChangingSlide = false;
          var wMove = false;
          var transitionEnd = function() {
            if (!wChangingSlide) return;
            var style = $('.swiper-wrapper')[0].style.transform;
            var start = style.indexOf('translate3d(') + 12;
            var end = style.indexOf(',', start);
            var x = parseInt(style.slice(start, end));
            var index = -x / document.body.offsetWidth - 1;
            if (index == -1) index = Object.keys(countries).length - 1;
            swiperIndex = index;
            setCountry(countries[Object.keys(countries)[index % Object.keys(countries).length]], true);
            wChangingSlide = false;
          };
          swiper = new Swiper('.swiper-container', {
            initialSlide: countryIndex,
            loop: true,
            on: {
              touchMove: function() {
                wMove = true;
              },
              touchEnd: function() {
                if (!wMove) return;
                wMove = false;
                wChangingSlide = true;
              },
              transitionEnd: transitionEnd,
            },
          });
          $('.country-card.' + currCountry.key + '-card').addClass('country-card-on');
          _.each(countries, function(country) {
            $('.' + country.key + '-jump').click(function() {
              location.href = country.key;
            });
          });
          setCountry(currCountry, true);

          if (localStorage.arriveDate) {
            var date = new Date(localStorage.arriveDate);
            if (date.getTime() < new Date().getTime() - 24 * 60 * 60000) {
              localStorage.arriveDate = '';
            } else {
              $('.start-date .date').text(date.getDate() + '/' + (date.getMonth() + 1) + '/' + date.getFullYear());
            }
          }
          if (localStorage.departDate) {
            var date = new Date(localStorage.departDate);
            if (date.getTime() < new Date().getTime() - 24 * 60 * 60000) {
              localStorage.departDate = '';
            } else {
              $('.end-date .date').text(date.getDate() + '/' + (date.getMonth() + 1) + '/' + date.getFullYear());
            }
          }

          $('.start-date .date').click(function() {
            if ($('.calendar1').css('display') == 'none') {
              $('.calendar1').show();
              setCalendar('calendar1', new Date(localStorage.arriveDate || new Date().getTime()), 'arriveDate');
            } else {
              $('.calendar1').hide();
            }
          });
          $('.end-date .date').click(function() {
            if ($('.calendar2').css('display') == 'none') {
              $('.calendar2').show();
              setCalendar('calendar2', new Date(localStorage.departDate || new Date().getTime()), 'departDate');
            } else {
              $('.calendar2').hide();
            }
          });

          document.onclick = function(evt) {
            var calendars = ['calendar1', 'calendar2'];
            _.each(calendars, function(calendar) {
              var wFound = false;
              for (var node = evt.target; node != document; node = node.parentNode) {
                var className = node.getAttribute('class');
                if (className && className.indexOf(calendar) != -1
                  || node == $('.' + calendar)[0].previousSibling.previousSibling) {
                  wFound = true;
                  break;
                }
              }
              if (!wFound) $('.' + calendar).hide();
            });
          }

          $('.people input').on('blur', function(evt) {
            localStorage.people = parseInt(evt.target.value, 0);
          });

          _.each(countries, function(country) {
            $('.' + country.key + '-dot').click(function() {
              setCountry(country);
            });
          });

          _.each(countries, function(country) {
            $('.' + country.key + '-video .play').click(function() {
              if (swiperTimeout) {
                clearTimeout(swiperTimeout);
                swiperTimeout = null;
              }
              setCountry(country);
              _.each(countries, function(country) {
                $('.' + country.key + '-video iframe').attr('src', '');
                $('.' + country.key + '-video .cover').removeClass('cover-on');
              });
              $('.' + country.key + '-video iframe').attr('src', country.video + '?rel=0&amp;controls=0&amp;showinfo=0;autoplay=1');
              $('.' + country.key + '-video .cover').addClass('cover-on');
            });
          });

          contactError = $('#contact-error').dialog({
            autoOpen: false,
            resizable: false,
            draggable: false,
            height: "auto",
            modal: true,
            show: {
              effect: "fadeIn", duration: 300
            },
            hide: {
              effect: "fadeOut", duration: 300
            },
            open: function(event, ui){
              setTimeout("$('#contact-error').dialog('close')", 1000);
            }
          });
          contactEmailError = $('#contact-email-error').dialog({
            autoOpen: false,
            resizable: false,
            draggable: false,
            height: "auto",
            modal: true,
            show: {
              effect: "fadeIn", duration: 300
            },
            hide: {
              effect: "fadeOut", duration: 300
            },
            open: function(event, ui){
              setTimeout("$('#contact-email-error').dialog('close')", 1000);
            }
          });
          contactSucceed = $('#contact-succeed').dialog({
            autoOpen: false,
            resizable: false,
            draggable: false,
            height: "auto",
            modal: true,
            show: {
              effect: "fadeIn", duration: 300
            },
            hide: {
              effect: "fadeOut", duration: 300
            },
            open: function(event, ui){
              setTimeout("$('#contact-succeed').dialog('close')", 1000);
            }
          });

          $('#contact-name').on('focus', function() {
            $('#contact-name').removeClass('error');
          });
          $('#contact-email').on('focus', function() {
            $('#contact-email').removeClass('error');
          });
          $('#contact-content').on('focus', function() {
            $('#contact-content').removeClass('error');
          });

          $('#contact-name').on('input', function() {
            $(this).val($(this).val().replace(/[0-9]/g, ''));
          });

          $('div.send').click(function() {
            var name = $('#contact-name').val();
            var email = $('#contact-email').val();
            var content = $('#contact-content').val();
            if (!name) {
              $('#contact-name').addClass('error');
            }
            if (!email) {
              $('#contact-email').addClass('error');
            }
            if (!content) {
              $('#contact-content').addClass('error');
            }
            if (!name || !email || !content) {
              contactError.dialog('open');
              return;
            }
            if (!validateEmail(email)) {
              contactEmailError.dialog('open');
              return;
            }
            $.post("php/contact_email.php", {
              name: name,
              email: email,
              content: content,
            }, function (){})
            .done(function(data) {
              contactSucceed.dialog("open");
              $('#contact-name').val('');
              $('#contact-email').val('');
              $('#contact-content').val('');
            });
          });
        });

        function initMap() {
          $(document).ready(function() {
            var mapOptions = {
              center: new google.maps.LatLng(59.348418, 18.074735),
              zoom: 13,
              mapTypeId: google.maps.MapTypeId.ROADMAP,
              mapTypeControl: false,
              fullscreenControl: false,
            };
            var map = new google.maps.Map(document.getElementById("map"), mapOptions);
            var marker = new google.maps.Marker({
              position: new google.maps.LatLng(59.348418, 18.074735),
              map: map,
              title: 'We are here',
            });
          });
        }
    </script>
    <!-- Google Map -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2CW7bgyFboHSichhVLV25pgcV3rfMlGY&callback=initMap"></script>
</head>

<body>

<?php
  include $pathPrefix . 'php/header.php';
?>
<div class="img-header">
    <div class="img-container swiper-container">
      <div class="swiper-wrapper">
        <?php
          echo join(array_map(function($country) {
            return '<div class="swiper-slide img" style="background-image: url(\'' . $country['backgroundImage'] . '\')"></div>';
          }, $countries), '');
        ?>
      </div>
    </div>
    <div class="cover"></div>
    <div class="intro">
        <p class="discover">Welcome to Sweden</p>
    </div>
    <div class="selector-container">
      <div class="country selector">
        <p class="title">Country</p>
        <div class="content">
          <i class="fas fa-map-marker-alt"></i>
          <select id="country-select">
            <option value="" selected disabled hidden>Select a country</option>
            <?php
              echo join(array_map(function($country) {
                return '<option value="' . $country['key'] . '">' . $country['name'] . '</option>';
              }, $countries) ,'');
            ?>
          </select>
        </div>
      </div>
      <div class="date-selector start-date selector">
        <p class="title">Arrival date</p>
        <div class="content">
          <i class="fas fa-calendar-alt"></i>
          <div class="date calendar1-date">Select a date</div>
          <div class="start-date calendar1 calendar">
            <div class="header">
              <div class="left jump"><</div>
              <div class="month"></div>
              <div class="right jump">></div>
            </div>
            <div class="body">
              <?php
                $row = 1;
                echo join(array_map(function($i) {
                  global $row;
                  $row = $i;
                  return join(array_map(function($col) {
                    global $row;
                    return '<div class="block ' . $row . '-' . $col .'">1</div>';
                  }, [0, 1, 2, 3, 4, 5, 6]), '');
                }, [0, 1, 2, 3, 4, 5]), '');
              ?>
            </div>
          </div>
          <div class="divide"></div>
        </div>
      </div>
      <div class="date-selector end-date selector">
        <p class="title">Departure date</p>
        <div class="content">
          <i class="fas fa-calendar-alt"></i>
          <div class="date calendar2-date">Select a date</div>
          <div class="end-date calendar2 calendar">
            <div class="header">
              <div class="left jump"><</div>
              <div class="month"></div>
              <div class="right jump">></div>
            </div>
            <div class="body">
              <?php
                $row = 1;
                echo join(array_map(function($i) {
                  global $row;
                  $row = $i;
                  return join(array_map(function($col) {
                    global $row;
                    return '<div class="block ' . $row . '-' . $col .'">1</div>';
                  }, [0, 1, 2, 3, 4, 5, 6]), '');
                }, [0, 1, 2, 3, 4, 5]), '');
              ?>
            </div>
          </div>
        </div>
      </div>
      <div class="people selector">
        <p class="title">Number of travelers</p>
        <div class="content">
          <i class="fas fa-users"></i>
          <input id="people" type="number" placeholder="Type a number" min="1" value="1" />
        </div>
      </div>
    </div>
    <div class="button">
      <a class="edge-btn-pink search" href="#">Discover</a>
    </div>
    <div class="dots">
      <?php
        echo join(array_map(function($country) {
          return '<div class="dot ' . $country['key'] . '-dot"></div>';
        }, $countries), '');
      ?>
    </div>
</div>

<div class="container">

    <div class="section introd">
      <div class="intro-container">
        <div class="title">Discover the Nordics with our packages</div>
        <div class="space"></div>
        <div class="text">Sit amet consectetur adipisicing elit, sed do eiusmo. Tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</div>
      </div>
    </div>

    <div class="section features">
      <div class="title">We've got everything you need, all in one place</div>
      <div class="feature-card-container">
        <?php
          echo join(array_map(function($feature) {
            return '
              <div class="feature-card"">
                <img src="' . $feature['image'] . '"></img>
                <div class="text">' . $feature['text'] . '</div>
              </div>
            ';
          }, $features), '');
        ?>
      </div>
    </div>

    <div class="section countries">
      <div class="title">Choose a country where you want to travel to</div>
      <div class="country-card-container">
        <?php
          echo join(array_map(function($country) {
            return '
              <div class="country-card ' . $country['key'] . '-card" style="background-image: url(\'' . $country['thumbnailImage'] . '\')">
                <div class="frame"></div>
                <div class="body">
                  <h2>' . $country['name'] . '</h2>
                  <a class="edge-btn-pink button ' . $country['key'] . '-jump" href="#' . $country['key'] . '">Choose</a>
                </div>
              </div>
            ';
          }, $countries), '');
        ?>
      </div>
    </div>

    <div class="section videos">
      <div class="title">Explore the Nordics</div>
      <div class='videos-holder'>
        <div class='playing-video'>
        <div class='playing-video-bg'>
          
        </div>
        <iframe src="https://www.youtube.com/embed/XRLAhcEPHlc?rel=0&amp;controls=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>  
        <div class="small-wrapper">
            <?php
              echo join(array_map(function ($country) {
                return ('
                  <div class="youtube-wrapper ' . $country['key'] . '-video">
                    <div class="cover" style="background-image: url(\'' . str_replace('embed', 'vi', str_replace('www.youtube', 'img.youtube', $country['video'])) . '/0.jpg\')">
                      <div class="play">
                        <img src="img/video-play.png"></img>
                      </div>
                    </div>
                    <div class="slide">' . $country['name'] . '</div>
                  </div>
                ');
              }, $countries), '');
            ?>
        </div>
      </div>
    </div>

    <div class="section contact">
        <div class="title">Contact us</div>
        <div class="contact-container">
          <div class="left">
            <div class="part">
              <div class="title">How shall we call you? *</div>
              <input id="contact-name" placeholder="Type your name here"></input>
            </div>
            <div class="part">
              <div class="title">What is your email address? *</div>
              <input id="contact-email" placeholder="We will keep it safe!"></input>
            </div>
            <div class="part">
              <div class="title">We are all ears! *</div>
              <textarea id="contact-content" placeholder="Share with us any information that might help us to respond you."></textarea>
            </div>
            <div class="button">
              <div class="edge-btn-pink send">Send messages</div>
            </div>
          </div>
          <div class="right">
            <div class="part coffee">
              <div class="title">Come in for a coffee <i class="fas fa-coffee"></i></div>
              <div id="map" class="map"></div>
              <div class="line">
                <i class="fas fa-map-marker-alt"></i>
                <a target="_blank" href="https://www.google.com/maps/place/Globuzzer/@59.3483476,18.0723981,16.5z/data=!4m5!3m4!1s0x465f9d8bb05819ff:0xfe256910b24b5df0!8m2!3d59.348301!4d18.0748591">
                  Lindstedtsvägen 24, 4th Floor,<br>114 28 Stockholm, Sweden
                </a>
              </div>
              <div class="line">
                <i class="fas fa-envelope"></i>
                <a target="_blank" href="mailto:rami@globuzzer.com?subject=Message about gb-brochure">rami@globuzzer.com</a>
              </div>
              <div class="line">
                <i class="fas fa-phone"></i>
                <a href="tel:+46735555134">+46 73 555 5 134</a>
              </div>
            </div>
          </div>
        </div>
    </div>
</div>

<?php
  include $pathPrefix . 'php/chat.php';
?>

<!-- Dialog for lack of information -->
<div id="contact-error">
    Please fill in all necessary information!
</div>

<!-- Dialog for invalid email address -->
<div id="contact-email-error">
    Please fill in valid email address!
</div>

<!-- Dialog for successfully send -->
<div id="contact-succeed">
    Successfully sent!
</div>

<?php
  include $pathPrefix . 'php/footer.php';
?>

</body>

</html>
