<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Controller\ArticleListAction;
use App\Controller\ArticleListOldAction;
use App\Controller\ArticleListOldIterableAction;
use App\Controller\ArticleListSymfonyAction;
use App\Doctrine\EntityManagerFactory;
use Symfony\Component\HttpFoundation\Response;

function bytes($bytes, $force_unit = '', $format = NULL, $si = TRUE)
{
    // Format string
    $format = ($format === NULL) ? '%01.2f %s' : (string) $format;

    // IEC prefixes (binary)
    if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE)
    {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $mod   = 1024;
    }
    // SI prefixes (decimal)
    else
    {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        $mod   = 1000;
    }

    // Determine unit to use
    if (($power = array_search((string) $force_unit, $units)) === FALSE)
    {
        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
    }

    return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
}

switch ($_SERVER['REQUEST_URI']) {
    case '/':
        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(<<<EOT
<!DOCTYPE html>
<html lang="en"><head><title>Articles</title></head><body>
<h1>Articles</h1><div id="loading">Loading ....</div><table id="list"></table><script src="script.js"></script>
</body></html>
EOT);
        $response->headers->set('Content-Type', 'text/html');

        $response->send();

        break;
    case '/articles.json':
        $entityManager = EntityManagerFactory::getEntityManagerFactory();
        $action = new ArticleListAction();
        $response = $action($entityManager);
        $response->send();

        $memoryUsage = memory_get_usage(true);
        $memoryPeakUsage = memory_get_peak_usage(true);

        file_put_contents(
            __DIR__ . '/../var/memory-usage.txt',
            bytes($memoryUsage) . PHP_EOL . bytes($memoryPeakUsage)
        );
        break;
    case '/symfony-articles.json':
        $entityManager = EntityManagerFactory::getEntityManagerFactory();
        $action = new ArticleListSymfonyAction();
        $response = $action($entityManager);
        $response->send();

        $memoryUsage = memory_get_usage(true);
        $memoryPeakUsage = memory_get_peak_usage(true);

        file_put_contents(
            __DIR__ . '/../var/memory-usage-symfony.txt',
            bytes($memoryUsage) . PHP_EOL . bytes($memoryPeakUsage)
        );

        break;
    case '/old-articles.json':
        $entityManager = EntityManagerFactory::getEntityManagerFactory();
        $action = new ArticleListOldAction();
        $response = $action($entityManager);
        $response->send();

        $memoryUsage = memory_get_usage(true);
        $memoryPeakUsage = memory_get_peak_usage(true);

        file_put_contents(
            __DIR__ . '/../var/memory-usage-old.txt',
            bytes($memoryUsage) . PHP_EOL . bytes($memoryPeakUsage)
        );

        break;
    case '/old-iterable-articles.json':
        $entityManager = EntityManagerFactory::getEntityManagerFactory();
        $action = new ArticleListOldIterableAction();
        $response = $action($entityManager);
        $response->send();

        $memoryUsage = memory_get_usage(true);
        $memoryPeakUsage = memory_get_peak_usage(true);

        file_put_contents(
            __DIR__ . '/../var/memory-usage-old-iterable.txt',
            bytes($memoryUsage) . PHP_EOL . bytes($memoryPeakUsage)
        );

        break;
    default:
        $response = new Response();
        $response->setStatusCode(404);
        $response->setContent('Error 404 - Page not found.');

        $response->send();

        break;
}
