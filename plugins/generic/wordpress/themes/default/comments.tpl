<div class="separator"></div>
<div id="commentsOnArticle">
<h3>{translate key="comments.commentsOnArticle"}</h3>
{php}
echo $this->getTemplatePath() . 'comments.php';
 comments_template();
{/php}
</div>