<?php

namespace App\Http\Controllers;

use FPDF;

class C_BillCreate extends FPDF
{
    private $client;

    private $articles;

    private $total;

    public function __construct()
    {
        parent::__construct();
        $this->total = 0;
    }

    public function createBill($client, $articles, $fileName = 'facture.pdf')
    {
        $this->client = $client;
        $this->articles = $articles;

        $this->AddPage();
        $this->addHeader();
        $this->addEntrepriseInfo();
        $this->addClientInfo();
        $this->addArticles();
        $this->addFooter();

        if (! file_exists($fileName)) {
            $this->Output('F', $fileName, true);
        } else {
            unlink($fileName);
        }

        $this->Output('D', $fileName, true);
    }

    public function addHeader()
    {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Facture', 0, 1, 'C');
        $this->Ln(10);
    }

    public function addEntrepriseInfo()
    {
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Entreprise : WIZIA', 0, 1);
        $this->Cell(0, 10, 'Adresse : 4 Chem. de la Chatterie, 44800 Saint-Herblain', 0, 1);
        $this->Cell(0, 10, 'Tel : 02 40 89 85 63', 0, 1);
        $this->Ln(10);
    }

    public function addClientInfo()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Facturer :', 0, 1);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, $this->client['nom'], 0, 1);
        $this->Cell(0, 10, $this->client['adresse'], 0, 1);
        $this->Ln(10);
    }

    public function addArticles()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(80, 10, 'Nom', 1, 0, 'C');
        $this->Cell(30, 10, 'Quantité', 1, 0, 'C');
        $this->Cell(40, 10, 'Prix Unitaire', 1, 0, 'C');
        $this->Cell(40, 10, 'Total', 1, 1, 'C');

        $this->SetFont('Arial', '', 12);

        foreach ($this->articles as $article) {
            $totalArticle = $article['quantite'] * $article['prix'];
            $this->total += $totalArticle;

            $this->Cell(80, 10, $article['nom'], 1, 0);
            $this->Cell(30, 10, $article['quantite'], 1, 0, 'C');
            $this->Cell(40, 10, number_format($article['prix'], 2).' €', 1, 0, 'R');
            $this->Cell(40, 10, number_format($totalArticle, 2).' €', 1, 1, 'R');
        }

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(150, 10, 'TOTAL', 1, 0, 'R');
        $this->Cell(40, 10, number_format($this->total, 2).' €', 1, 1, 'R');
    }

    public function addFooter()
    {
        $this->SetY(200);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Page '.$this->PageNo(), 0, 0, 'C');
    }
}
