
<div class="container">

   {if $total_pages > 1}
   <nav aria-label="Blog pages">
     <ul class="pagination justify-content-center">
       <li class="page-item{if ($page-1) lt 1} disabled{/if}">
        <a class="page-link" href="{if ($page-1) lt 1}#{else}http://{$WEB_ROOT}{$PUBLIC_DIR}/category/{$cat_string}{if $active_tags_string neq ''}/tags/{$active_tags_string}{/if}/{$page-1}{/if}">
          <i class="fa fa-angle-left" aria-hidden="true"></i> Newer
        </a>
       </li>
       <li class="page-item{if $page+1 gt $total_pages} disabled{/if}">
         <a class="page-link" href="{if $page+1 gt $total_pages}#{else}http://{$WEB_ROOT}{$PUBLIC_DIR}/category/{$cat_string}{if $active_tags_string neq ''}/tags/{$active_tags_string}{/if}/{$page+1}{/if}">
           Older <i class="fa fa-angle-right" aria-hidden="true"></i>
         </a>
       </li>
     </ul>
   </nav>
   {/if}

</div>

