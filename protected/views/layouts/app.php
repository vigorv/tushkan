<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="language" content="ru"/>
    <meta name="viewport" content="user-scalable=no, width=device-width, minimum-scale=1.0, maximum-scale=1.0"/>
    <link rel="stylesheet" href="/css/iphone.css"/>
    <link rel="stylesheet" media="all and (orientation:portrait)" href="portrait.css">
    <link rel="stylesheet" media="all and (orientation:landscape)" href="landscape.css">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png"/>
    <script langauge="javascript">
        window.addEventListener('orientationchange', ChangeOrientation, false);

        function ChangeOrientation() {
            var orientation = Math.abs(window.orientation) === 90 ? 'landscape' : 'portrait';
            // alert(orientation);
        }
        function NoElasticScroll(e) {
         e.preventDefault();
        }
        var Device = {};

        Device.isiPhone = function() {
            return navigator.userAgent.indexOf('iPhone') != -1;
        }
        Device.isiPod = function() {
            return navigator.userAgent.indexOf('iPod') != -1;
        }
        Device.isiPad = function() {
            return navigator.userAgent.indexOf('iPad') != -1;
        }
        Device.isiOS = function() {
            return Device.isiPhone || Device.isiPod || Device.isiPad;
        }
        </script>

</head>
<body ontouchmove="NoElasticScroll(event);">
<?php echo $content;?>
</body>
</html>