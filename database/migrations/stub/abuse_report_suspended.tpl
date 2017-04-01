{extends "email:base_alert.tpl"}

{block "alert-type"}danger{/block}

{block "alert-message"}[ABUSE] Server Suspended!{/block}

{block "body"}
    <tr>
        <td class="content-block">
            {$client.name},
        </td>
    </tr>
    <tr>
        <td class="content-block">
            {$server.name} has been suspended due to an outstanding Abuse Report from {$report.date} that went unanswered.
        </td>
    </tr>
{/block}
