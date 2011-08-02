<?php


$memcache = new Memcache;

$memcache->connect('unix:///home/j0shua/memcached.sock', 0) or die ("Could not connect");

$time = time();

$id = 23;
$times = array();
for ($i = 0; $i<10; $i++)
{
    $new_time = $time - $i;
    $times[$new_time] = $new_time;
}
//$data = array_combine($times, $times);
asort($times);
$data = $times;

$memcache->set($id, serialize($data), FALSE, 3);

echo "time is $time\n";
echo "data is: " . var_export($data, true);
echo "\n";

$retreived = unserialize($memcache->get($id));
echo "retreived data is:" . var_export($retreived, true);

echo "\n";
echo "sleeping for one sec .....\n";
sleep(1);

echo "purging all items less than 2 seconds ago .....\n";
$threshold = 2;
$low_mark = $time - $threshold;
$retreived = purge($retreived, $low_mark);
echo "data is now: " . var_export($retreived, true);
$memcache->set($id, serialize($retreived), FALSE, 2);

$retreived = $memcache->get($id);
echo "retreived data is:" . var_export(unserialize($retreived), true);

echo "\n";
echo "sleeping for two sec .....\n";
sleep(2);
$retreived = $memcache->get($id);
echo "\nretreived data is:" . var_export($retreived, true);

echo "\n";
echo "bye\n";

exit;


function purge($data, $threshold)
{

    foreach ($data as $k => $time)
    {
        if ($k < $threshold)
        {
            unset($data[$k]);
            echo "purged $k\n";
        }
        else 
        {
            break;
        }

    }
    return $data;
}
