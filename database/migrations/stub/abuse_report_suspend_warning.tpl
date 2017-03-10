{extends "email:base_alert.tpl"}

{block "alert-type"}danger{/block}

{block "alert-message"}Abuse Report Suspend Warning!{/block}

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
            <b>Days left</b> {$days}
        </td>
    </tr>
{/block}
