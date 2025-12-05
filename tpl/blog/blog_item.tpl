{include file="header.tpl"}

<ul class="pagination">
{if isset($blog) and $internal_referrer}
    <li class="page-item">
        <a class="page-link back" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog">
            <i class="fa fa-angle-left" aria-hidden="true"></i>
            Back
        </a>
    </li>
{/if}
</ul>

<div class="row d-flex justify-content-between">



    <div class="col-sm-7">

        {include file="elib:blog/comp_blog_heading.tpl"}

        <div class="blog-post">
            <h2 class="blog-post-title">
                {$blog->heading}
            </h2>

            <p class="blog-post-meta">{$blog->stamp|sdate:$def_date_format}</p>

            <div class="content">
                {$blog->body|blog_images:$WEB_ROOT:$PUBLIC_DIR}
            </div>

            {*{include file="elib:blog/comp_social_buttons.tpl"}*}

            {if $disqusUsername neq ''}
                {include file="elib:blog/comp_disqus.tpl" disqusUsername=$disqusUsername}
            {/if}

            <p class="entry_meta">
                {assign var=itemCats value=$blog->getCats()}
                {if isset($itemCats) and count($itemCats)}
                    <span class="me-2">Categories:</span>
                    {foreach from=$itemCats key=i item=c}
                        <span class="tag">
                            <span class="badge bg-{if $i eq $blog_category}success{else}secondary{/if}">
                                <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/category/{$c|lower}">
                                    {$c}
                                </a>
                            </span>
                        </span>
                    {/foreach}
                {/if}

                {assign var=itemTags value=$blog->getTags()}
                {if isset($itemTags) and count($itemTags)}
                    <span class="sep">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                    <span class="me-2">Tags:</span>
                    {foreach from=$itemTags item=t}
                        <span class="tag">
                            <span class="badge bg-{if isset($active_tags) and count($active_tags) and in_array($t, $active_tags)}info{else}secondary{/if}">
                                <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/tags/{$t}">
                                    {$t}
                                </a>
                            </span>
                        </span>
                    {/foreach}
                {/if}
            </p>

            {if 0 and $connect}
                <p>Sign in with twitter to leave comments.</p>
                <p><a href="#" id="connect">
                        <img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/elib/lighter.png" alt="Sign in with Twitter"/></a></p>
            {/if}

            {section name=comment_item loop=$comments}
                <div class="entry">

                    {*<h2>{$comments[comment_item].heading} <span>|</span> {$comments[comment_item].stamp|date_format:"%d/%m/%Y, %k:%M"} <span>|</span> {$comments[comment_item].username}</h2>*}

                    <h4>{$comments[comment_item].username}
                        wrote<br/>on {$comments[comment_item].stamp|date_format:"%A %e %B %Y at %k:%M"}</h4>
                    {$comments[comment_item].body|replace:"</p>":"</p>\n"}
                </div>
            {/section}

            {if 0 and $user_id > 0}

                {if isset($errors) and count($errors) > 0}
                    <ul id="error">
                        {foreach from=$errors item=error}
                            <li>{$error}</li>
                        {/foreach}
                    </ul>
                {/if}
                <form action="" method="post">
                    <fieldset>
                        <legend></legend>
                        <p>
                            <label>Heading</label>
                            <input type="text" name="heading" value="{$comment->heading}"/>
                        </p>
                        <p>
                            <label>Body</label>
                            <textarea rows="0" cols="0"
                                      name="body">{$comment->body|replace:'</p><p>':"\r\n"|replace:'<p>':""|replace:'</p>':""}</textarea>
                        </p>

                        <p>
                            <label>&nbsp;</label>
                            <button type="submit" name="submit">Submit</button>
                        </p>
                    </fieldset>
                </form>
            {/if}
        </div>

        {*

  <nav>
    <ul class="pager">
      <li><a href="#">Previous</a></li>
      <li><a href="#">Next</a></li>
    </ul>
  </nav>
*}

    </div><!-- /.blog-main -->

    {include file="elib:blog/sidebar.tpl"}

</div><!-- /.row -->


{include file="footer.tpl"}
