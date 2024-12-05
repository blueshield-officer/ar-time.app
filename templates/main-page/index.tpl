<!DOCTYPE html>
<html lang="en">
<head>
    {include file='constructor/header.tpl'}
    {assign var='version' value='1.21'}
    <link rel="stylesheet" href="/css/index.css?v={$version}">
    <title>ar-time</title>
    <script>
      let data = JSON.parse('{$schedule_JSON}');
      let absence = {
        data: JSON.parse('{$absence['absence_JSON']}'),
        md5: '{$absence['absence_MD5']}',
        version_time: '{$absence['version_time']}'
      }
      let news = ''
      let partners = ''
      let event_bar = ''
      console.log('Schedule Data ↓')
      console.log(data)
      console.log('')
      console.log('Absence Data ↓')
      console.log(absence)
      console.log('')
      console.log('')
      console.log('codice spaghetti')

    </script>
</head>
<body>
{include file="$navbar"}
{if $event_bar !== ''}
    <script>event_bar = '{$event_bar}'</script>
    {include file="universal/event-bar.tpl"}
{/if}
<div class="container">
    <div class="row">
        <div class="col-md-5">
            {include file="main-page/countdown-box.tpl"}
        </div>
        <div class="col-md-7">
            {if is_array($table_data)}
                {include file="main-page/bell-table.tpl" tableData = $table_data}
            {else}
                {include file="main-page/bell-table-null.tpl"}
            {/if}
        </div>
        <div class="col-md-12 justify-content-center">
            {include file="main-page/absence.tpl"}
        </div>
    </div>
    {if $news !== null}
        <script>news = JSON.parse('{$news}')</script>
        <div class="row">
            <div class="col-12 justify-content-center">
                {include file="main-page/news.tpl"}
            </div>
        </div>
    {/if}
    {if $partners !== null}
        <script>partners = JSON.parse('{$partners}')</script>
        <div class="row">
            <div class="col-12 justify-content-center">
                {include file="main-page/partners.tpl"}
            </div>
        </div>
    {/if}
</div>
<!---->
{include file="universal/botbar.tpl"}

<script src="/js/countdown-box.js?v={$version}"></script>
<script src="/js/navbar.js?v={$version}"></script>
<script src="/js/absent.js?v={$version}"></script>
<script src="/js/botbar.js?v={$version}"></script>
<script src="/js/eventbar.js?v={$version}"></script>
<script src="/js/news.js?v={$version}"></script>
<script src="/js/partners.js?v={$version}"></script>
</body>
</html>