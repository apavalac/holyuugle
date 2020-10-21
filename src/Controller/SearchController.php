<?php

namespace App\Controller;

use App\Entity\Holiday;
use App\Repository\HolidayRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class SearchController extends AbstractController
{
    public static $standartMonths = array(
        1 => 'Jan.',
        2 => 'Feb.',
        3 => 'Mar.',
        4 => 'Apr.',
        5 => 'May',
        6 => 'Jun.',
        7 => 'Jul.',
        8 => 'Aug.',
        9 => 'Sep.',
        10 => 'Oct.',
        11 => 'Nov.',
        12 => 'Dec.'
    );

    /**
     * @Route("/search", name="search")
     */
    public function index(Request $request)
    {

        if($request->request->get('country') == null)
        {
            return $this->redirect('/');
        }

        $minLimit = [];
        // Check if record is already in db if not call API
        if($this->dataExists($request->request->get('country'), $request->request->get('year')))
        {
            $repository = $this->getDoctrine()->getRepository(Holiday::class);
            $holiday = $repository->findOneBy(
                [
                    'country' => $request->request->get('country'),
                    'year' => $request->request->get('year')
                ]
            );

            // Get stored json data
            $data = $holiday->getData();

        }
        else
        {
            // Get data from API
            $data = json_decode(($this->getResult($request))->getContent(), true);

            if(count($data) != 1)
            {
                // Record data
                $this->createRecord($request->request->get('country'), $request->request->get('year'), $data);
            }
            else
            {
                $minLimit = explode(' ', $data['error']);
            }
        }

        // Get all holidays grouped by month
        $months = $this->getHolidays($data);

        // Get current day status(free day, work day, holiday)
        $status = $this->getStatus($request->request->get('country'));

        $holidays = 0;
        foreach( $months as $month)
            $holidays += count($month);

        $maxim = $this->getMaxSequence($data);

        return $this->render('/home/search.html.twig', [
            'minLimit' => $minLimit,
            'maxHolidays' => $maxim,
            'holidaysCount' => $holidays,
            'currentDay' => $status,
            'monthsDays' => $months,
            'months' => self::$standartMonths,
            'supported' => MainController::$countries
        ]);
    }

    public function getResult($data)
    {

        $client = HttpClient::create();
        $url = 'https://kayaposoft.com/enrico/json/v2.0?action=getHolidaysForYear&year=' . $data->request->get('year') . '&country=' . $data->request->get('country') . '&holidayType=public_holiday';

        $response = $client->request('GET', $url);

        return($response);
    }

    public function getHolidays($json)
    {

        $months = [];

        if(count($json) > 1)
        {
            foreach($json as $day)
            {
                if(isset($months[$day['date']['month'] - 1]))
                    array_push($months[$day['date']['month'] - 1], $day);
                else
                    $months[$day['date']['month'] - 1] = array($day);
            }
        }

        return ($months);
    }

    public function getStatus($country)
    {
        $currentStatus = 'Free Day';
        $client = HttpClient::create();
        $url = 'https://kayaposoft.com/enrico/json/v2.0?action=isPublicHoliday&date=' . date('d-m-Y') . '&country=' . $country;

        $response = $client->request('GET', $url);
        $json = json_decode($response->getContent(), true);

        if(array_values($json)[0])
            $currentStatus = 'Public Holiday';

        $url = 'https://kayaposoft.com/enrico/json/v2.0?action=isWorkDay&date=' . date('d-m-Y') . '&country=' . $country;
        $response = $client->request('GET', $url);
        $json = json_decode($response->getContent(), true);

        if(array_values($json)[0])
            $currentStatus = 'Work Day';

        return ($currentStatus);
    }

    public function getMaxSequence($json)
    {
        $max = 0;
        $dayDiference = 86400;

        $current = 1;
        for($i = 0; $i < count($json) - 1; $i++)
        {
            $date1 = strtotime(($json[$i]['date']['day']) . '-' . $json[$i]['date']['month'] . '-' . $json[$i]['date']['year']);
            $date2 = strtotime(($json[$i + 1]['date']['day']) . '-' . $json[$i + 1]['date']['month'] . '-' . $json[$i + 1]['date']['year']);

            if($date2 - $date1 == $dayDiference)
            {
                $current++;

                if($i == count($json) - 2   )
                {
                    if($current > $max)
                        $max = $current;
                    $current = 1;
                }
            }
            else
            {
                if($current > $max)
                    $max = $current;
                $current = 1;
            }
        }

        return ($max);
    }

    public function dataExists($country, $year)
    {
        $repository = $this->getDoctrine()->getRepository(Holiday::class);
        $holiday = $repository->findOneBy(
            [
                'country' => $country,
                'year' => $year
            ]
        );

        if($holiday)
            return (1);

        return (0);
    }

    public function createRecord($country, $year, $json)
    {
        // Initiate entityManager
        $em = $this->getDoctrine()->getManager();

        // Create new entity
        $holidays = new Holiday();

        // Set data
        $holidays->setCountry($country);
        $holidays->setYear($year);
        $holidays->setData($json);

        // Initiate doctrine
        $em->persist($holidays);

        // Save to database
        $em->flush();

        return ($holidays->getId());
    }
}
