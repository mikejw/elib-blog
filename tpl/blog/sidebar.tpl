        

         <div class="col-sm-3 col-sm-offset-1 blog-sidebar">
          <div class="sidebar-module sidebar-module-inset">
            <h4>About</h4>
            {$about|truncate:300:"..."} <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/about">more</a></p>

          </div>
          <div class="sidebar-module">

            {include file="elib:/blog/comp_tags.tpl"}

          </div>
          <div class="sidebar-module">
            <h4>Elsewhere</h4>
            <ol class="list-unstyled">
             {foreach from=$social item=link key=name}
            <li><a href="{$link}">{$name}</a></li>
            {/foreach}
            </ol>
          </div>
        </div><!-- /.blog-sidebar -->