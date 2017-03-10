{extends "email:base_alert.tpl"}

{block "alert-type"}danger{/block}

{block "alert-message"}Abuse Report Suspended!{/block}

{block "body"}
    <tr>
        <td class="content-block">
            Client - {$client.name},
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Server - {if $server}, {$server.name|escape}{/if}.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <b>Abuse Report Created Date</b> {$report.date}
        </td>
    </tr>
{/block}
