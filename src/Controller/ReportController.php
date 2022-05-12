<?php

namespace App\Controller;

use App\Entity\Reports;
use App\Form\ReportsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class ReportController extends AbstractController
{


    public function index(): Response
    {
        //return $this->render('report/index.html.twig', [
        //    'controller_name' => 'ReportController',
       // ]);
    }

    /**
     * @Route("/uploadreport", name="uploadreport")
     */
    public function NewReport(Request $request){
        $Report= new Reports();
        $form=  $this->CreateForm(ReportsType::class,$Report);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $Report->getFirstName();
            $Report->getLastName();
            $userEmail=$Report->getUserEmail();
            $file=$Report->getName();
            $filename= md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('upload_directory'),$filename);
            $filePath = $this->getParameter('upload_directory').$filename;
            $Report->setName($filename);
            $Report->setFilePath($filePath);
            $em = $this->getDoctrine()->getManager();
            $em->persist($Report);
            $em->flush();
            return $this->redirectToRoute('list_reports');
        }
        return $this->render('report/uploadreport.html.twig',array('form'=>$form->createView()));
    }
    /**
     * @Route("/reportslist", name="list_reports")
     */
    public function ReportsList(Request $request):Response {
        $reports = $this->getDoctrine()->getRepository(Reports::class)->findBy([],['id'=>'DESC']);
        return $this->render('report/reportlist.html.twig',['reports' => $reports]);

    }

    /**
     * @Route("/readreport/{id}", name="read_report")
     */
    public function ReadFile(Reports $id,MailerInterface $mailer){
    $objectManager = $this->getDoctrine()->getManager();

        $Report = $objectManager->getRepository(Reports::class)->find($id);
        //$file = $Report->getFilePath();
        $reader = new Xlsx();
        $reader->setReadDataOnly(TRUE);
        $spreadsheet = $reader->load($Report->getFilePath());
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $firstimage = [];
        $secondimage = [];
        // getting data from excel file for second image
        $firstimage[$worksheet->getCellByColumnAndRow(4,7)->getCalculatedValue()]= $worksheet->getCellByColumnAndRow(6,7)->getCalculatedValue();
        $chart1 = '{"type": "doughnut","data": {"labels": ["Achived","Left to Achieve   "],"datasets": [{"data": [';
        foreach($firstimage  as $key => $value)
        {
            $chart1=$chart1.'"'.$key.'","'.$value.'"';
        }
        $chart1=$chart1.']}]},"options":{"cutoutPercentage":80,"circumference":1*Math.PI,"rotation":1*Math.PI,},"styling":{"weight":1}}';
        $enc1=urlencode($chart1);
        $imageUrl1 = "https://quickchart.io/chart?c=" . $enc1;
        // getting data from excel file for second image
        $secondimage[$worksheet->getCellByColumnAndRow(1,3)->getCalculatedValue()]= $worksheet->getCellByColumnAndRow(3,3)->getCalculatedValue();
        $secondimage[$worksheet->getCellByColumnAndRow(1,4)->getCalculatedValue()]= $worksheet->getCellByColumnAndRow(3,4)->getCalculatedValue();
        $secondimage[$worksheet->getCellByColumnAndRow(1,5)->getCalculatedValue()]= $worksheet->getCellByColumnAndRow(3,5)->getCalculatedValue();
        $secondimage[$worksheet->getCellByColumnAndRow(1,6)->getCalculatedValue()]= $worksheet->getCellByColumnAndRow(3,6)->getCalculatedValue();
        // Generating Image for email
        $chart2 = '{"type": "bar","data": {"labels": [';
        foreach($secondimage as $key => $value)
        {
          $chart2=$chart2.'"'.$key.'",';
        }
        $chart2=$chart2.'],"datasets": [{backgroundColor: ["#1e7034", "#2379a1", "#a18823", "#82128a"],"strokeColor": "rgba(220,220,220,1)","pointColor": "rgba(220,220,220,1)","pointStrokeColor": "#fff","bezierCurve": false,"data": [';
        foreach($secondimage as $key => $value)
        {
            $chart2=$chart2.''.$value.',';
        }
        $chart2 = $chart2.']}]},"options":{"plugins":{"legend":false}}}';
        $enc2=urlencode($chart2);
        $imageUrl2 = "https://quickchart.io/chart?c=" . $enc2;
        // get all the table
        $tablehead1=[];
        $tabledata1=[];

        for($col = 1; $col <= 6; $col++) {
                $value = $worksheet->getCellByColumnAndRow($col,2)->getCalculatedValue();
                array_push($tablehead1,$value);
            }

        for($row=3; $row <= 7 ; $row++){
            for($col = 1; $col <= 6; $col++) {
                $value = $worksheet->getCellByColumnAndRow($col,$row)->getCalculatedValue();
                array_push($tabledata1,$value);
            }
        }
        $email = (new TemplatedEmail())
            ->from('TEST ADNEN <adnenettayeb@gmail.com>')
            ->to(new Address($Report->getUserEmail()))
            ->subject('Monthly Report')
            ->htmlTemplate('report/ReportTemplate.html.twig')
            ->context([
                'image1' => $imageUrl1,
                'image2' => $imageUrl2,
                'firstname'=>$Report->getFirstname(),
                'lastname'=>$Report->getLastname(),
                'useremail'=> $Report->getUserEmail(),
                'tablehead1'=>$tablehead1,
                'tabledata1'=>$tabledata1
        ]);
        $mailer->send($email);

        return $this->redirectToRoute('list_reports');




    }

}
