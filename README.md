# README #

##API usage examples##

**Get Review:**

 http://<path-to-api>/?review

 (Default period is one hour)


**Get Review Using Filters:**

  http://<path-to-api>/?review&filter=zun


**Get Review Using Start time and End time:**

  http://<path-to-api>/?review&start=2016-07-17%2012:01&end=2016-07-17%2014:00:00


**Get Review Using time interval in seconds:**

  http://<path-to-api>/?review&interval=7200


**Get FlapChart by port and host:**

  http://<path-to-api>/?flaphistory&host=192.168.168.21&ifindex=509


**Get FlapChart Using Start time and End time:**

  http://<path-to-api>/?flaphistory&host=192.168.168.21&ifindex=509&start=2016-07-17%2012:01&end=2016-07-17%2014:00:00

**Check API:**

  http://isweethome.ihome.ru/api/?check