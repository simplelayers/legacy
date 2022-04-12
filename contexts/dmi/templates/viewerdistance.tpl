<distance numsegments="<!--{$numsegments}-->">
   <total feet="<!--{$total.feet}-->" miles="<!--{$total.miles}-->" meters="<!--{$total.meters}-->" kilometers="<!--{$total.kilometers}-->">

   <segments>
   <!--{section name=i loop=$segments}-->
      <!--{assign var=segment value=$segments[i]}-->
      <segment from="<!--{$segment.from}-->" to="<!--{$segment.to}-->"
        feet="<!--{$segment.feet}-->" miles="<!--{$segment.miles}-->" meters="<!--{$segment.meters}-->" kilometers="<!--{$segment.kilometers}-->"
      />
   <!--{/section}-->
   </segments>
</distance>
