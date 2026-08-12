<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

if (! function_exists('output_pdf')) {
	function output_pdf($html, $name = '', $download = true) {
		$dompdf = new Dompdf();
		$options = $dompdf->getOptions();
		// $options->setDefaultFont('Arial');
		$dompdf->setOptions($options);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		if ($download) {
			$dompdf->stream('invoice_' . $name . '.pdf');
		} else {
			// file_put_contents('invoice_' . $name . '.pdf', $dompdf->output());
			return $dompdf->output();
		}
	}
}

if (! function_exists('output_manifest')) {
	function output_manifest($html, $name = '', $download = true) {
		$dompdf = new Dompdf();
		$options = $dompdf->getOptions();
		// $options->setDefaultFont('Arial');
		$dompdf->setOptions($options);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// (Optional) Setup the paper size and orientation
		// $dompdf->setPaper('A4', 'landscape');
		$dompdf->setPaper(
			[
				0,
				0,
				298,
				442
			],
			'portrait'
		);

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		if ($download) {
			$dompdf->stream('manifest_' . $name . '.pdf');
		} else {
			// file_put_contents('invoice_' . $name . '.pdf', $dompdf->output());
			return $dompdf->output();
		}
	}
}
