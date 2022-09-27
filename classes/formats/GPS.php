<?php
namespace formats;

class GPS extends LayerFormat
{

    public $inputTemplate = 'import/gps1.tpl';

    public $reimportTemplate = 'import/gps1.tpl';

    public function __construct()
    {
        $this->label = 'GPS Format';
    }

    public function Import($args, $world = null, \Person $user = null)
    {
        if (is_null($world))
            $world = \System::Get();
        if (is_null($user))
            $user = \SimpleSession::Get()->GetUser();
            /*
         * // if they're already over quota, or if their account doesn't allow this, then bail
         * if ($user->diskUsageRemaining() <= 0) {
         * $error = 'Your account is already at the maximum allowed storage.\nPlease delete some layers and try again.';
         * print javascriptalert($error);
         * return print redirect('layer.list');
         * }
         *
         * // print a busy image to keep their eyes amused
         * busy_image('Your file is being imported. Please wait.');
         */
        $tempfile = $world->config['tempdir'] . '/' . md5(microtime() . mt_rand()) . '.gpx';
        move_uploaded_file($_FILES['source']['tmp_name'], $tempfile);
        
        // if it's not a GPX, translate it to GPX
        if ($_REQUEST['gpsformat'] == 'mps') {
            if ($_REQUEST['type'] == 'waypoint')
                $flag = '-w';
            if ($_REQUEST['type'] == 'track')
                $flag = '-t';
            if ($_REQUEST['type'] == 'route')
                $flag = '-r';
            $newtempfile = $world->config['tempdir'] . '/' . md5(microtime() . mt_rand()) . '.gpx';
            $command = escapeshellcmd("gpsbabel $flag -i Mapsource -f \"$tempfile\" -o gpx -F \"$newtempfile\"");
            `$command`;
            $tempfile = $newtempfile;
        }
        
        // use ogr2ogr to translate the GPX into a shapefile
        if ($_REQUEST['type'] == 'waypoint')
            $sublayer = 'waypoints';
        if ($_REQUEST['type'] == 'track')
            $sublayer = 'tracks';
        if ($_REQUEST['type'] == 'route')
            $sublayer = 'routes';
        if ($_REQUEST['type'] == 'waypoint')
            $sublayer = '-w';
        if ($_REQUEST['type'] == 'track')
            $sublayer = '-t';
        if ($_REQUEST['type'] == 'route')
            $sublayer = '-r';
            // ping("$sublayer");
        $shapefile = sprintf("%s/%s.shp", $world->config['tempdir'], md5(microtime() . mt_rand()));
        $command = sprintf("%s %s -o %s %s", 'gpx2shp', escapeshellarg($tempfile), escapeshellarg($shapefile), $sublayer);
        shell_exec($command);
        // create the new layer entry, and use shp2pgsql() to import the data from the shapefile
        
        // $_REQUEST['name'] = $user->uniqueLayerName($_REQUEST['name']);
        $desiredname = $_REQUEST['name'];
        $layerexist = $user->layerExists($desiredname);
        
        // ping ("$desiredname");
        $layerexist = isset($_REQUEST['layerid']);
        if ($layerexist == true) {
            $layer = $user->getLayerById($_REQUEST['layerid']);
            $layerno = $layer->id;
            // ping("$layerno");
            $layer->setDBOwnerToDatabase();
            $table = $layer->url;
            $result = $world->db->Execute("drop table $table"); // ?"array($layerno)); //didn't work with array
            if (! $result)
                ping($world->db->ErrorMsg());
                // the following line refreshes the layer details page
            $layer->name = $desiredname;
        } else {
            if ($user->community && count($user->listLayers()) >= 3) {
                print javascriptalert('You cannot create more than 3 layers with a community account.');
                return print redirect('layer.list');
            }
            // create the Layer object we'll be populating
            $layer = $user->createLayer($desiredname, \LayerTypes::VECTOR);
            $layer->colorscheme->setSchemeToSingle();
            $table = $layer->url;
        }
        
        // $layer = $user->createLayer($_REQUEST['name'],LayerTypes::VECTOR);
        // ping("importing...<br/>\n");
        shp2pgsql($world, $shapefile, $layer->url);
        // ping("optimizing...<br/>\n");
        $layer->optimize();
        $layer->fixDBPermissions();
        $layer->ValidateColumnNames();
        // send them to the editing page for their new layer
        return print redirect("layer.edit1&id={$layer->id}");
    }
}
?>
