<?xml version='1.0' encoding="ISO-8859-1" ?>
<wfs:FeatureCollection
   xmlns:ms="http://mapserver.gis.umn.edu/mapserver"
   xmlns:wfs="http://www.opengis.net/wfs"
   xmlns:gml="http://www.opengis.net/gml"
   xmlns:ogc="http://www.opengis.net/ogc"
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www.opengis.net/wfs http://schemas.opengeospatial.net/wfs/1.0.0/WFS-basic.xsd http://mapserver.gis.umn.edu/mapserver <!--{$onlineresource|escape:'htmlall'}-->REQUEST=DescribeFeatureType&amp;typename=<!--{$typename}-->">

<gml:boundedBy>
<gml:Box srsName="EPSG:4326">
<gml:coordinates><!--{$bboxlx}-->,<!--{$bboxly}--> <!--{$bboxux}-->,<!--{$bboxuy}--></gml:coordinates>
</gml:Box>
</gml:boundedBy>

<!--{section name=i loop=$records}-->
<!--{assign var=record value=$records[i]}-->
<gml:featureMember>
<ms:<!--{$typename}--> fid="<!--{$typename}-->.<!--{$record.gid}-->">
    <gml:boundedBy>
    <gml:Box srsName="EPSG:4326"><gml:coordinates><!--{$record.bbox.0}-->,<!--{$record.bbox.1}--> <!--{$record.bbox.2}-->,<!--{$record.bbox.3}--></gml:coordinates></gml:Box>
    </gml:boundedBy>
    <ms:msGeometry>
    <!--{$record.gml_geom}-->
    </ms:msGeometry>
    <!--{section name=q loop=$attributes}-->
    <!--{assign var=attribute value=$attributes[q]}-->
    <!--{assign var=value value=$record.$attribute}-->
    <ms:<!--{$attribute}-->><!--{$value|escape:'html'}--></ms:<!--{$attribute}-->>
    <!--{/section}-->
</ms:<!--{$typename}-->>
</gml:featureMember>
<!--{/section}-->

</wfs:FeatureCollection>
