<?xml version="1.0" encoding="UTF-8" ?>
<results sql="<!--{$sql|escape:'htmlall'}-->">
  <!--{section name=i loop=$results }-->
  <!--{assign var=result value=$results[i]}-->
  <result 
    <!--{section name=p loop=$fields}-->
    <!--{assign var=field value=$fields[p]}-->
    <!--{$field}-->="<!--{$result.$field|escape:'htmlall'}-->"
    <!--{/section}-->
  />
  <!--{/section}-->
</results>
