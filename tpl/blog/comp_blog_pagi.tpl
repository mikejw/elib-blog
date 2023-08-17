
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
   <nav aria-label="Page navigation example">
     <ul class="pagination justify-content-center">
       <li class="page-item{if ($page-1) lt 1} disabled{/if}">
        <a class="page-link" href="{if ($page-1) lt 1}#{else}http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $active_tags_string neq ''}tags/{$active_tags_string}{else}blog{/if}/{$page-1}{/if}">
          <i class="fa fa-angle-left" aria-hidden="true"></i> Newer
        </a>
       </li>
       <li class="page-item{if $page+1 gt $total_pages} disabled{/if}">
         <a class="page-link" href="{if $page+1 gt $total_pages}#{else}http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $active_tags_string neq ''}tags/{$active_tags_string}{else}blog{/if}/{$page+1}{/if}">
           Older <i class="fa fa-angle-right" aria-hidden="true"></i>
         </a>
       </li>
     </ul>
   </nav>
   {/if}


    {*
    {if $active_tags_string neq ''}
    <ul class="pager" style="margin-top: 5px;">
      <li class="previous"><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog">Clear active tag{if $multi_tags}s{/if}</a></li>
    </ul>
    {/if}
    *}
</div>

