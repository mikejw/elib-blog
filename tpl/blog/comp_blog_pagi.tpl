
<div class="container">

     {* old style 
    <ul class="pagination" style="margin-botton: 0px;">
      <li class="{if ($page-1) lt 1}disabled{/if}"><a href="{if ($page-1) lt 1}#{else}http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $active_tags_string neq ''}tags/{$active_tags_string}{else}blog{/if}/{$page-1}{/if}">&laquo;</a></li>
      {foreach from=$pages item=i key=k}
      <li class="{if $k eq $page}disabled{/if}"><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $active_tags_string neq ''}tags/{$active_tags_string}{else}blog{/if}/{$k}">{$k}</a></li>
      {/foreach}
      <li class="{if $page+1 gt $total_pages}disabled{/if}"><a href="{if $page+1 gt $total_pages}#{else}http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $active_tags_string neq ''}tags/{$active_tags_string}{else}blog{/if}/{$page+1}{/if}">&raquo;</a></li>
    </ul>
   *}

   {if $total_pages > 1}
    <nav>
    <ul class="pager">
      <li class="{if $page+1 gt $total_pages}disabled{/if}"><a href="{if $page+1 gt $total_pages}#{else}http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $active_tags_string neq ''}tags/{$active_tags_string}{else}blog{/if}/{$page+1}{/if}">Previous</a></li>
      <li class="{if ($page-1) lt 1}disabled{/if}"><a href="{if ($page-1) lt 1}#{else}http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $active_tags_string neq ''}tags/{$active_tags_string}{else}blog{/if}/{$page-1}{/if}">Next</a></li>
    </ul>
    </nav>
    {/if}


    {if $active_tags_string neq ''}
    <p>&nbsp;</p>
      <p style="text-align: center;">
        <a class="btn btn-default" href="http://{$WEB_ROOT}{$PUBLIC_DIR}">
          Clear active tag{if $multi_tags}s{/if}
        </a>
      </p>
    {/if}

    {*
    {if $active_tags_string neq ''}
    <ul class="pager" style="margin-top: 5px;">
      <li class="previous"><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog">Clear active tag{if $multi_tags}s{/if}</a></li>
    </ul>
    {/if}
    *}
</div>

