<!DOCTYPE html>
<html>
<head>
    <title>Chose date</title>
    <script src="https://code.jquery.com/jquery-3.5.1.js" crossorigin="anonymous"></script>
    <link id="bs-css" href="https://netdna.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link id="bsdp-css" href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
    <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" crossorigin="anonymous"></script>
</head>
<body>
    <input class="datepicker" data-date-format="mm/dd/yyyy">

    <script>
        var isSupported = false;
        var psid = null;
        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.com/en_US/messenger.Extensions.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, "script", "Messenger"));

        window.extAsyncInit = function () {
            isSupported = MessengerExtensions.isInExtension();
            // the Messenger Extensions JS SDK is done loading
            MessengerExtensions.getContext('1663449813836439',
                function success(result){
                    console.log('log success')
                    console.log(result)
                    psid = result.psid;
                },
                function error(result){
                    console.log(JSON.stringify(result));
                }
            );
        };

        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true
            });

            $('.datepicker').datepicker()
                .on('changeDate', function (e) {
                    var chose_date = moment(e.date);
                    console.log(chose_date.format('DD/MM/Y'))
                    if (isSupported && psid) {
                        sendDate(chose_date.format('DD/MM/Y'));
                    } else {
                        console.log('Not webview facebook messenger')
                    }
                });
        });

        function sendDate(date) {
            $.post('/api/v1/webview/date', {
                date: date,
                psid: psid
            })
                .done(function (data, textStatus, jqXHR) {
                    MessengerExtensions.requestCloseBrowser(function success() {
                    }, function error(err) {
                    });
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    window.close()
                });
        }

    </script>
</body>
</html>
