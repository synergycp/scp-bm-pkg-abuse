{extends "email:base_alert.tpl"}

{block "alert-type"}warning{/block}

{block "alert-message"}[ABUSE] Upcoming Suspension Warning!{/block}

{block "body"}
    <tr>
        <td class="content-block">
            {$client.name},
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <p>{$server.name} is at risk of being suspended due to an outstanding Abuse Report from {$report.date} that has gone unanswered.</p>
            <p>Please respond to the report with the steps taken to remove the abusing application within the next <b>{$days} day{($days == 1) ? '' : 's'})</b> or your server will be automatically suspended.</p>
        </td>
    </tr>
{/block}
