
<ul>
    {foreach from=$FacebookBoxes key=myId item=i}
            <div class="fb-page" data-href="{$i.FACEBOOKBOX_PAGE_URL}"
                 data-tabs="timeline"
                 data-small-header="{$i.FACEBOOKBOX_USE_SMALL_HEADER}"
                 data-adapt-container-width="{$i.FACEBOOKBOX_ADAPT_CONTAINER_WIDTH}"
                 data-width="{$i.FACEBOOKBOX_WIDTH}";
                 data-height="{$i.FACEBOOKBOX_HEIGHT}";
                 data-hide-cover="{$i.FACEBOOKBOX_HIDE_COVER_PHOTO}"
                 data-show-facepile="true">
                    <blockquote cite="{$i.FACEBOOKBOX_PAGE_URL}"
                                class="fb-xfbml-parse-ignore">
                            <a href="{$i.FACEBOOKBOX_PAGE_URL}">
                                    Facebook Profile</a>
                    </blockquote>

            </div>
    {/foreach}
</ul>
