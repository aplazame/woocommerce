<?php
global $post;

$articles = array( Aplazame_Aplazame_Api_BusinessModel_Article::createFromProduct( new WC_Product( $post ) ) );
?>

<div id="aplazame_campaigns_tab" class="panel woocommerce_options_panel">
	<div class="options_group" id="aplazame_campaigns_container">
	</div>
</div>

<script>
	var campaignsContainer = document.getElementById("aplazame_campaigns_container");

	var articles = <?php echo json_encode( $articles ) ?>;

	function associateArticlesToCampaign(articles, campaignId) {
		apiRequest("POST", "/me/campaigns/" + campaignId + "/articles", articles, function () {
		});
	}

	function removeArticlesFromCampaign(articles, campaignId) {
		var articleIds = articles.map(function (article) {
			return article.id;
		});

		apiRequest("DELETE", "/me/campaigns/" + campaignId + "/articles?article-mid=" + articleIds.join(","), null, function () {
		});
	}

	/**
	 * @param {MouseEvent} event
	 */
	function campaignToggle(event) {
		/**
		 * @type {HTMLInputElement|EventTarget}
		 */
		var checkbox = event.target;

		var campaignId = checkbox["data-campaignId"];
		if (checkbox.checked) {
			associateArticlesToCampaign(articles, campaignId);
		} else {
			removeArticlesFromCampaign(articles, campaignId);
		}
	}

	function insertCampaign(campaign) {

		var inputId = "campaign_" + campaign.id;
		/**
		 * @type {HTMLInputElement|Element}
		 */
		var checkbox = document.createElement("input");
		checkbox.type = "checkbox";
		checkbox.name = "campaigns[]";
		checkbox.value = campaign.id;
		checkbox.id = inputId;
		checkbox.className = "checkbox";
		checkbox["data-campaignId"] = campaign.id;
		checkbox.addEventListener("click", campaignToggle, false);

		if (!campaign.partial) {
		    checkbox.checked = true;
		    checkbox.disabled = true;
		    checkbox.title = "<?php echo __( 'The campaign applies to all products from your catalogue', 'aplazame' ) ?>";
		}

		/**
		 * @type {HTMLLabelElement|Element}
		 */
		var label = document.createElement("label");
		label.htmlFor = inputId;
		label.appendChild(document.createTextNode(campaign.name));

		var p = document.createElement("p");
		p.className = "form-field";
		p.appendChild(label);
		p.appendChild(checkbox);

		campaignsContainer.appendChild(p);
	}

	function displayCampaigns(campaigns) {
		campaigns.forEach(insertCampaign);
	}

	function selectCampaigns(campaigns) {
		campaigns.forEach(function (campaign) {
			var inputId = "campaign_" + campaign.id;
			document.getElementById(inputId).checked = true;
		});

	}

	function apiRequest(method, path, data, callback) {
		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			data: {
				action: "aplazame-proxy",
				method: method,
				path: path,
				data: JSON.stringify(data)
			},
			success: callback
		});
	}

	apiRequest("GET", "/me/campaigns", null, function (payload) {
		var campaigns = payload.results;

		apiRequest("GET", "/me/campaigns?articles-mid=" + articles[0].id, null, function (payload) {
			var selectedCampaigns = payload.results;

			displayCampaigns(campaigns);
			selectCampaigns(selectedCampaigns);
		});
	});
</script>
