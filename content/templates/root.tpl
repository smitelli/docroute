<!DOCTYPE html>
<html lang="en">
  <head>
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
