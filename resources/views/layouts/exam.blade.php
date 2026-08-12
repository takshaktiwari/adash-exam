<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Latest compiled and minified CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/htmx.org@1.9.5"
        integrity="sha384-xcuj3WpfgjlKF+FXhSQFQ0ZNr39ln+hwjN3npfM9VBnUskLolQAcN80McRIVOPuO" crossorigin="anonymous">
    </script>
    @stack('styles')
</head>

<body hx-boost="true">
    <x-alertt-alert />

    {{ $slot }}


    <script>
        $("#fullscreen_btn").click(function(){
            switchFullscreen();
        });

        var elem = document.documentElement;

        function switchFullscreen()
        {
            var fullscreen = $("#fullscreen_btn").attr('data-fullscreen');
            if(fullscreen == '1')
            {
                closeFullscreen();
                $("#fullscreen_btn").attr('data-fullscreen', '0');
            }else{
                openFullscreen();
                $("#fullscreen_btn").attr('data-fullscreen', '1');
            }
        }

        /* View in fullscreen */
        function openFullscreen() {
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                /* Safari */
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                /* IE11 */
                elem.msRequestFullscreen();
            }
        }

        /* Close fullscreen */
        function closeFullscreen() {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                /* Safari */
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                /* IE11 */
                document.msExitFullscreen();
            }
        }
    </script>

    <script>
        // htmx has no timeout or error UI by default, so a slow or dropped request
        // (common on mobile switching networks) leaves whichever button was clicked
        // stuck on "Please wait" forever with no way to recover except a full reload.
        // This bounds every htmx-boosted exam action with a timeout and restores the
        // UI instead of leaving it dead when a request fails or times out.
        htmx.config.timeout = 15000; // 15s — generous for a slow mobile connection, but bounded

        function examRecoverFromFailedRequest(message) {
            document.querySelectorAll('.action_btn[data-original-html]').forEach(function(el) {
                el.innerHTML = el.getAttribute('data-original-html');
                el.removeAttribute('data-original-html');
                el.classList.remove('disabled');
            });
            alert(message);
        }

        document.body.addEventListener('htmx:timeout', function() {
            examRecoverFromFailedRequest('That took too long to respond. Please check your connection and try again.');
        });
        document.body.addEventListener('htmx:sendError', function() {
            examRecoverFromFailedRequest('Could not reach the server. Please check your connection and try again.');
        });
        document.body.addEventListener('htmx:responseError', function() {
            examRecoverFromFailedRequest('Something went wrong saving that. Please try again.');
        });
    </script>
    @stack('scripts')
</body>

</html>
