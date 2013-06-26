{**
 * @file plugins/importexport/crossref/templates/settings.tpl
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * CrossRef plugin settings
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.common.settings"}
{include file="common/header.tpl"}
{/strip}
{** TODO: move this over to the FBV for OJS 3.0 **}
<div id="crossrefSettings">
	{include file="common/formErrors.tpl"}
	<br />
	<br />

	<div id="description"><b>{translate key="plugins.importexport.crossref.settings.form.description"}</b></div>

	<br />

	<script>
		$(function() {ldelim}
			// Attach the form handler.
			$('#crossrefSettingsForm').pkpHandler('$.pkp.controllers.form.FormHandler');
		{rdelim});
	</script>
	<form class="pkp_form" id="crossrefSettingsForm" method="post" action="{plugin_url path="settings"}">
		<table class="data">
			<tr>
				<td colspan="2">
					<span class="instruct">{translate key="plugins.importexport.crossref.intro"}</span>
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr>
				<td class="label">{fieldLabel name="username" key="plugins.importexport.crossref.settings.form.username"}</td>
				<td class="value">
					<input type="text" name="username" value="{$username|escape}" size="20" maxlength="50" id="username" class="textField" />
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr>
				<td class="label">{fieldLabel name="password" key="plugins.importexport.common.settings.form.password"}</td>
				<td class="value">
					<input type="password" name="password" value="{$password|escape}" size="20" maxlength="50" id="password" class="textField" />
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
		</table>

		<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

		{** the old button was named input **}
		<input type="hidden" name="save" value="1" />
		{fbvFormButtons submitText="common.save"}
	</form>

</div>
{include file="common/footer.tpl"}
