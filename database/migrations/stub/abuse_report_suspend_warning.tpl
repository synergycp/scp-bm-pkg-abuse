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
            <p>Please respond to the report with the steps taken to remove the abusing application within the next <b>{$days} day{($days == 1) ? '' : 's'}</b> or your server will be automatically suspended.</p>
            <p>If you do not know where this abuse is coming from, we recommend an immediate reinstall of the operating system on your server as it is likely that it has been compromised by an attacker and is being used for illegal activity.</p>
        </td>
    </tr>
{/block}
