{extends "email:base_alert.tpl"}

{block "alert-message"}
  New comment on Abuse Report
{/block}

{block "body"}
<tr>
	<td class="content-block">
		Greetings {$client.name},
	</td>
</tr>
<tr>
	<td class="content-block">
		An administrator has commented on an Abuse Report on your server{if $server}, {$server.name}{/if}.
	</td>
</tr>
<tr>
	<td class="content-block">
		<blockquote>{$comment.body}</blockquote>
	</td>
</tr>
<tr>
	<td class="content-block">
		<a href="{route('client.abuse.show', $report.id)}"
      class="btn-primary">
      View Report
    </a>
	</td>
</tr>
{/block}
