


{if !($module eq 'user' && $class eq 'user' && $event eq 'login')}
    </div>
{/if}

<script type="text/javascript" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/js/common.js"></script>
<script type="text/javascript" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/vendor/js/main.min.js?version={$dev_rand}"></script>
<script src="https://cdn.jsdelivr.net/npm/bs5-lightbox@1.8.5/dist/index.bundle.min.js"></script>


<style type="text/css">

    .ekko-lightbox {
        padding-right: 0 !important;
    }
    .ekko-lightbox .modal-dialog {
        width: 90% !important;
        max-width: 90% !important;
        height: 90% !important;

    }
    .ekko-lightbox .modal-body {
        padding: 0;
    }

    .ekko-lightbox-container iframe {
        width: 100% !important;
    }

    .ekko-lightbox .modal-content, .ekko-lightbox-container,
    .ekko-lightbox-container .ekko-lightbox-item.show,
    .ekko-lightbox-container .ekko-lightbox-item.show iframe {
        height: 100% !important;
    }
</style>


<script type="application/javascript">

    $(document).ready(function() {
        $(document).delegate('*[data-bs-toggle="lightbox"]', 'click', function (event) {

            alert('lightbox!');

            event.preventDefault();
            const lightbox = new Lightbox(this, {
                size: 'fullscreen',       // or 'lg', 'fullscreen', etc.
                keyboard: true,
                constrain: false,
                ratio: false // may not need this for videos (use '16x9')
            });

            lightbox.show();
        });

      $('.revision-select select').on('change', function() {
        var $this = $(this);
        if (confirm('Are you sure you want to load a different blog item revision?')) {
          window.location.href = '?revision=' + $this.val();
        }

        return false;
      });



    });
</script>

</body>
</html>