



{if !($module eq 'user' && $class eq 'user' && $event eq 'login')}
    </div>
{/if}

{if !(isset($centerpage) and $centerpage)}
    <footer class="mt-5">
        <div class="p-5 pb-1">
            {foreach from=$installed item=lib}
                {$lib.name} <em class="text-secondary">{$lib.version}</em>
            {/foreach}
        </div>
        <div class="p-5 pt-1">
            <a class="text-white" href="https://empathy.sh" target="_blank">emapthy.sh</a>
        </div>
    </footer>
{/if}

</div>

<script type="text/javascript" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/js/common.js"></script>
<script type="text/javascript" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/vendor/js/main.min.js?version={$dev_rand}"></script>
<script src="https://cdn.jsdelivr.net/npm/bs5-lightbox@1.8.5/dist/index.bundle.min.js"></script>

<script type="application/javascript">

  $(document).ready(function() {
    $(document).on('click', '[data-bs-toggle="lightbox"]', function (event) {

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

