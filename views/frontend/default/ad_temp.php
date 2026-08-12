<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="description" content="Display a GPT-managed web interstitial ad." />
	<title>Display a Web Interstitial Ad</title>
	<script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js" crossorigin="anonymous"></script>

	<script>
		window.googletag = window.googletag || {cmd: []};
		var interstitialSlot, staticSlot;

		googletag.cmd.push(function() {
			// 1. Define the interstitial out-of-page slot
			interstitialSlot = googletag.defineOutOfPageSlot(
				'/23361982758/pop_up_ad',
				googletag.enums.OutOfPageFormat.INTERSTITIAL
			);

			// Only configure if the device/page supports interstitials
			if (interstitialSlot) {
				interstitialSlot.addService(googletag.pubads());

				document.getElementById("status").innerText = "Interstitial slot is loaded!";

				// Update UI state when the slot loads
				googletag.pubads().addEventListener("slotOnload", function(event) {
					console.log({event})
					if (interstitialSlot === event.slot) {
						document.getElementById("link").style.display = "block";
						document.getElementById("status").innerText = "Interstitial is loaded!";
					}
				});
			}

			// 2. Define the static ad slot (Make sure the div exists in the body if you display it)
			staticSlot = googletag.defineSlot(
				'/23361982758/pop_up_ad',
				[[250, 250], [320, 480], [480, 320]],
				'div-gpt-ad'
			).addService(googletag.pubads());

			googletag.pubads().enableSingleRequest();
			googletag.enableServices();

			googletag.display('div-gpt-ad');
		});
	</script>

	<style>
		/* Keep trigger link hidden until interstitial is fully loaded */
		#link {
			display: none;
			font-size: 1.5rem;
			color: blue;
		}
		.modal {
			display: none;
			position: fixed;
			z-index: 1;
			padding-top: 300px;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			background-color: rgba(0, 0, 0, 0.4);
		}
		.modal[data-type] {
			display: block;
		}
		.modalDialog {
			margin: auto;
			padding: 25px;
			background-color: white;
			text-align: center;
			position: fixed;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
		}
		.grantButtons, .rewardButtons {
			display: none;
		}
		.modal[data-type="grant"] .grantButtons,
		.modal[data-type="reward"] .rewardButtons {
			display: block;
		}
		.modal input[type="button"] {
			padding: 0.5rem;
			background: blue;
			border: none;
			border-radius: 4px;
			margin: 4px;
			color: white;
		}
	</style>
</head>

<body>
	<h1 id="status">Interstitial is loading...</h1>

	<!-- Link explicitly excluded from triggering interstitials -->
	<a data-google-interstitial="false" href="https://bribooks.com">
		This link will never trigger an interstitial
	</a>
	<br><br>

	<!-- This internal/external link triggers the loaded interstitial on click -->
	<a id="link" href="https://bribooks.com">TRIGGER INTERSTITIAL</a>

	<!-- Static Ad Slot Container (Required if you run googletag.display(staticSlot)) -->
	<div id="div-gpt-ad" style="min-width: 250px; min-height: 250px; margin-top: 50px;">
		<!-- <script>
			googletag.cmd.push(function() {
				googletag.display(staticSlot);
			});
		</script> -->
	</div>
</body>
</html>
