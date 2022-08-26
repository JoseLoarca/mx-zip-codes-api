## Zip Codes API

This a simple API built with Laravel which only has one purpose: return zip codes' information.
The goal of this API is to provide the lowest response time possible.

### Technologies used
* PHP 8.1
* Laravel Framework 9.26.1
* MySQL 8
* Redis 6.2.6
* Python 3.7 (for benchmarking)

### How to run
1. Clone this repository on your machine
2. Install the required dependencies

    `composer install`
3. Enter your database and Redis credentials on the `.env` file
4. Run migrations

   `php artisan migrate`
5. Restore the provided MySQL [dump](https://github.com/JoseLoarca/mx-zip-codes-api/blob/master/ZipCodesDump.sql)

### My approach to this problem

The development of this API was done following the next steps:

1. **System design**: since the goal is to provide the lowest response time possible, the most appropriate feature
   for this would be cache. For my caching, I chose working with Redis.
2. **Database design**: the database design was based on the demo API
   and [this](https://www.correosdemexico.gob.mx/SSLServicios/ConsultaCP/CodigoPostal_Exportar.aspx) source of zip
   codes. This was the most tricky part, since there was little to no explanation on how settlements and localities are
   related to each other.

   <a href="https://github.com/JoseLoarca/mx-zip-codes-api/blob/master/ZipCodesER.png"><img src="https://raw.githubusercontent.com/JoseLoarca/mx-zip-codes-api/master/ZipCodesER.png" width="400"></a>

3. **Models creation**: models and migrations were created based on the diagram created on the previous step.
4. **Read and insert data**: with the database schema done and the models already developed, it was time to insert the
   data from the provided source of zip codes. I
   slightly [modified](https://github.com/JoseLoarca/mx-zip-codes-api/blob/master/CodigosPostalesMX.txt) the file (
   actually, just the headers were removed) and with the help of Laravel commands, the data was read from the file and
   inserted into the database.
5. **Controller and endpoint creation**: with data ready to be used, the next step was to work on the single controller
   this API has. This task was divided in 2 steps:
    1. Resources creation: a few API resources are used for the API response. In this step, the first relevant
       optimization was done: zip codes are directly related to settlements. In order to avoid n+1 queries, the
       settlements relationship is always loaded for zip codes. This helps to avoid extra queries, specially for those
       zip codes with multiple settlements associated.
    2. Controller creation: once the resources were ready, the zip-codes controller was coded. At first, this was a
       normal invokable controller that retrieved the zip code information using route implicit binding and then
       returned the resource.
6. **Cache**: now that the controller was working as expected (returning well formatted information), it was time to
   implement caching. The first step for this was to stop using implicit binding for the controller. Implicit binding
   automatically hits the MySQL database and for this I want to first check on Redis if the zip code is cached. Changes
   were made so that the controller does the following:
    1. When receiving a request, the first step is to check if the zip code resource is cached.
    2. If the resource is cached, it's retrieved from Redis and then parsed to it can be returned as a JSON response.
    3. If the resource is not cached, the next is to check if the requested zip code exists in database.
    4. If the requested zip code doesn't exist, a 404 status is returned.
    5. If it exists, the resource is fetched and then stored in cache, with no expiration date (since this data doesn't
       change).
    6. After the resource is cached, the zip resource is returned as a JSON response.
7. **Optimizations**: after the caching was implemented. A few optimizations were done, these optimizations mainly
   consisted on remove some Laravel autoloaders. Since this is a simple API with only one endpoint, irrelevant services
   providers like the AuthServiceProvider, the MailServiceProvider and a few more were removed.
8. **Testing**: a few test cases were created to validate the behavior of the API.
9. **Local benchmarks**: after everything was done, some benchmarks were run using a Python script that does 2 sets of
   requests. The first set tracks the response time for non cached resources and triggers the caching of those
   resources. The seconds set tracks the response time for the previously cached resources. On a local environment, the
   improvement of speed after caching the resources ranges from 50% to up to 78%.
10. **Deployment**: the API and the database are currently hosted in an AWS EC2 t4g.small instance. The instance is
    running Amazon Linux 2, PHP 8.1 + nginx & PHP-FPM and MySQL 8. Redis is working in an AWS ElastiCache cache.t3.micro
    cluster.

### Final thoughts

This was a simple yet challenging project. There are a few optimizations that still could be done, mostly related to the
architecture, like using RDS for the MySQL database and using better instance types for both the EC2 and ElastiCache
services.
Once deployed the performance really changes, the improvement of speed % is _way less_ once deployed vs running on a
local environment. I'd like to see how the system behaves when deployed using better specs.
