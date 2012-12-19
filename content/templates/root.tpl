<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>
      {if $cfg->title}
        {$cfg->title} | {$cfg->siteTitle}
      {else}
        {$cfg->siteTitle}
      {/if}
    </title>
  </head>

  <body>
    {$subtemplate}
  </body>
</html>