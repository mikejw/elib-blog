{include file="header.tpl"}


    
      <div class="row">

        <div class="col-sm-8 blog-main">

        {if $secondary_title neq ''}
        <h2>{$secondary_title}</h2>
        {/if}

        {foreach from=$blogs item=blog_item}
        <div class="blog-post">
          <h2 class="blog-post-title">
            {if $blog_item.slug neq ''}
            {*<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$blog_item.stamp|date_format:"%Y"}/{$blog_item.month_slug}/{$blog_item.stamp|date_format:"%d"}/{$blog_item.slug}">*}
              {$blog_item.heading}
            {*</a>*}
            {else}
            {*<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$blog_item.blog_id}">*}
              {$blog_item.heading}
            {*</a>*}
            {/if}
          </h2>

          <p class="blog-post-meta">
            {$blog_item.stamp|sdate:$def_date_format}
          </p>

          {*<h3>{$blog_item.stamp|date_format:"%A %e %B, %Y"}</h3>*}

          {$blog_item.body}
          {if $blog_item.truncated eq 1}
          
          <p>
            {if $blog_item.slug neq ''}
              <a class="btn btn-default" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$blog_item.stamp|date_format:"%Y"}/{$blog_item.month_slug}/{$blog_item.stamp|date_format:"%d"}/{$blog_item.slug}">
                Read more...
              </a>
            {else}
              <a class="btn btn-default" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$blog_item.blog_id}">
                Read more...
              </a>
            {/if}
          </p>
          {/if}
        

          <p class="entry_meta">
          {*
          {if $blog_item.slug neq ''}
          <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$blog_item.stamp|date_format:"%Y"}/{$blog_item.month_slug}/{$blog_item.stamp|date_format:"%d"}/{$blog_item.slug}">Permalink</a>
          {else}
          <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$blog_item.blog_id}">Permalink</a>
          {/if}
          &nbsp;&nbsp;|&nbsp;&nbsp;*}

          {*
          &nbsp;&nbsp;|&nbsp;&nbsp;
          Category: 
          {counter start=1 print=false assign=i}
          {foreach from=$blog_item.categories item=c}
          <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/category/{$c|lower}">{$c}</a>

          {if $i neq sizeof($blog_item.categories)}, {/if} 
          {counter}
          {/foreach}
          *}

          {if count($blog_item.tags)}

          Tags: 
          {counter start=1 print=false assign=i}
          {foreach from=$blog_item.tags item=t}
          <span class="tag">
            <span class="label label-{if count($active_tags) and in_array($t, $active_tags)}info{else}default{/if}">
              <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/tags/{$t}">
                  {$t}
              </a>
            </span>
          </span>
          {*
          {if $i neq sizeof($blog_item.tags)}, {/if} 
          {counter}
          *}
          {/foreach}
          {/if}
          {*
          &nbsp;&nbsp;|&nbsp;&nbsp;
          {$blog_item.comments} comment{if $blog_item.comments neq 1}s{/if}
          *}
          </p>
        </div>
    

        {foreachelse}
        <p style="text-align: center;">No posts found.</p>
        {/foreach}

          {if $active_tags_string neq ''}
        <p style="text-align: center;">
          <a class="btn btn-default" href="http://{$WEB_ROOT}{$PUBLIC_DIR}">
            Clear active tag{if $multi_tags}s{/if}</a>
          </a>
        </p>
        {/if}

{*
        <nav>
          <ul class="pager">
            <li><a href="#">Previous</a></li>
            <li><a href="#">Next</a></li>
          </ul>
        </nav>
*}
        



         

        </div><!-- /.blog-main -->

        {include file="elib:/blog/sidebar.tpl"}

      </div><!-- /.row -->

   
 {include file="footer.tpl"}