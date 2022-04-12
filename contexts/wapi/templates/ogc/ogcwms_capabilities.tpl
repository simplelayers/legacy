<?xml version='1.0' encoding="ISO-8859-1" standalone="no"?>
<!DOCTYPE WMT_MS_Capabilities SYSTEM "http://schemas.opengis.net/wms/1.1.1/WMS_MS_Capabilities.dtd"
 [
 <!ELEMENT VendorSpecificCapabilities EMPTY>
 ]>
<!-- end of DOCTYPE declaration -->
<WMT_MS_Capabilities version="1.1.1">

	<Service>
		<Name>OGC:WMS</Name>
		<Title><!--{$title|escape:'htmlall'}--></Title>
		<!--{$OnlineResource}-->
		<OnlineResource xlink:href="<!--{$onlineresource|escape:'htmlall'}-->" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:type="simple"  />
		<ContactInformation>
		</ContactInformation>
	</Service>

	<Capability>
		<Request>
			<GetCapabilities>
				<Format>application/vnd.ogc.wms_xml</Format>
				<DCPType>
					<HTTP>
						<Get>
							<OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
								xlink:type="simple" xlink:href="<!--{$onlineresource|escape:'htmlall'}-->" />
						</Get>
					</HTTP>
				</DCPType>
			</GetCapabilities>
			<GetMap>
				<Format>image/png; mode=24bit</Format>
				<Format>image/jpeg</Format>
				<DCPType>
					<HTTP>
						<Get>
							<OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
								xlink:type="simple" xlink:href="<!--{$onlineresource|escape:'htmlall'}-->" />
						</Get>
					</HTTP>
				</DCPType>
			</GetMap>
			<GetFeatureInfo>
				<Format>application/vnd.ogc.gml</Format>
				<DCPType>
					<HTTP>
						<Get>
							<OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
								xlink:type="simple" xlink:href="<!--{$onlineresource|escape:'htmlall'}-->" />
						</Get>	<!--{* <Post><OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<!--{$onlineresource|escape:'htmlall'}-->
						"/>
					</Post>
						*}-->
					</HTTP>
				</DCPType>
			</GetFeatureInfo>
			<DescribeLayer>
				<Format>text/xml</Format>
				<DCPType>
					<HTTP>
						<Get>
							<OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
								xlink:type="simple" xlink:href="<!--{$onlineresource|escape:'htmlall'}-->" />
						</Get>
					</HTTP>
				</DCPType>
			</DescribeLayer>
			<GetLegendGraphic>
				<Format>image/png; mode=24bit</Format>
				<DCPType>
					<HTTP>
						<Get>
							<OnlineResource xmlns:xlink="http://www.w3.org/1999/xlink"
								xlink:type="simple" xlink:href="<!--{$onlineresource|escape:'htmlall'}-->" />
						</Get>
					</HTTP>
				</DCPType>
			</GetLegendGraphic>
		</Request>
		<Exception>
			<Format>application/vnd.ogc.se_xml</Format>
			<Format>application/vnd.ogc.se_blank</Format>
		</Exception>
		<VendorSpecificCapabilities />
		<UserDefinedSymbolization SupportSLD="0"
			UserLayer="0" UserStyle="0" RemoteWFS="0" />

		<Layer>
			<Name><!--{$title|escape:'htmlall'}--></Name>
			<Title><!--{$title|escape:'htmlall'}--></Title>
			<SRS>EPSG:4326</SRS>
			<LatLonBoundingBox minx="<!--{$bboxlx}-->" miny="<!--{$bboxly}-->"
				maxx="<!--{$bboxux}-->" maxy="<!--{$bboxuy}-->" />
			<BoundingBox SRS="EPSG:4326" minx="<!--{$bboxlx}-->"
				miny="<!--{$bboxly}-->" maxx="<!--{$bboxux}-->" maxy="<!--{$bboxuy}-->" />

			<!--{section name=i loop=$projectlayers}-->
			<!--{assign var=projectlayer value=$projectlayers[i]}-->
			<!--{assign var=layer value=$projectlayer->layer}-->
			<Layer queryable="<!--{if $layer->type == $smarty.const.LAYERTYPE_VECTOR}-->1<!--{else}-->0<!--{/if}-->"
				opaque="0" cascaded="<!--{if $layer->type == $smarty.const.LAYERTYPE_WMS}-->1<!--{else}-->0<!--{/if}-->"
				>
				<Name><!--{$projectlayer->id|escape:'htmlall'}--></Name>
				<Title><!--{$layer->name|escape:'htmlall'}--></Title>
				<SRS>EPSG:4326</SRS>
				<!--{$layer|ll_bbox_wms_111}-->
				<!--{if $layer->type == $smarty.const.LAYERTYPE_VECTOR and $projectlayer->labelitem}-->
				<Style>
					<Name>labels</Name>
					<Title>Enable labels. If not given, the layer's default is used.</Title>
				</Style>
				<Style>
					<Name>nolabels
				</Name>
					<Title>Disable labels. If not given,the layer's default is used.</Title>
				</Style>
				<Style>
					<Name>highquality
				</Name>
					<Title>Force the use of a slower rendering engine with better
						quality on lines and curves.</Title>
				</Style>
				<Style>
					<Name>lowquality
				</Name>
					<Title>Force the use of a faster rendering engine with lower
						quality on lines and curves. This is the default.</Title>
				</Style>
				<Style>
					<Name>labels-highquality
				</Name>
					<Title>See the labels style and the highquality style.</Title>
				</Style>
				<Style>
					<Name>nolabels-highquality
				</Name>
					<Title>See the nolabels style and the highquality style.</Title>
				</Style>
				<Style>
					<Name>labels-lowquality
				</Name>
					<Title>See the labels style and the lowquality style.</Title>
				</Style>
				<Style>
					<Name>nolabels-lowquality
				</Name>
					<Title>See the nolabels style and the lowquality style.</Title>
				</Style>
				<!--{/if}-->

			</Layer>
			<!--{/section}-->

		</Layer>
	</Capability>
</WMT_MS_Capabilities>