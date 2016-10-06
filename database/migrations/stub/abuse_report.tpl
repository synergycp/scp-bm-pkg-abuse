{extends "email:base_alert.tpl"}

{block "alert-type"}danger{/block}

{block "alert-message"}Abuse Report Received!{/block}

{block "body"}
<tr>
	<td class="content-block">
		Greetings {$client.name},
	</td>
</tr>
<tr>
	<td class="content-block">
		We have received a report of abuse against your server{if $server}, {$server.name}{/if}.
	</td>
</tr>
<tr>
	<td class="content-block">
		<b>Date Received:</b> {$report.date}
	</td>
</tr>
<tr>
	<td class="content-block">
		<a href="{$urls.view}" class="btn-primary">
      View Report
    </a>
	</td>
</tr>
{/block}
