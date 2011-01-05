<div id="topNavbar">
	<ul class="topNavbar">
		{if $isUserLoggedIn}
			<li class="userName">{translate key="plugins.block.user.loggedInAs"} <strong>{$loggedInUsername|escape}</strong></li>
			<li><a href="{url page="user" op="profile"}">{translate key="plugins.block.user.myProfile"}</a></li>
			<li><a href="{url page="login" op="signOut"}">{translate key="plugins.block.user.logout"}</a></li>
			{if false && $userSession->getSessionVar('signedInAs')}
				<li><a href="{url page="login" op="signOutAsUser"}">{translate key="plugins.block.user.signOutAsUser"}</a></li>
			{/if}
		{else}
			{if $implicitAuth}	
				<a href="{url page="login" op="implicitAuthLogin"}">Journals Login</a>		
			{else}
				<li><a href="{url page="login"}">{translate key="navigation.login"}</a></li>
				<li class="noBorder"><a href="{url page="user" op="register"}">{translate key="navigation.register"}</a></li>
			{/if}
		{/if}
	</ul>
</div>