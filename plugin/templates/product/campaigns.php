<?php
global $post;

$articles = array();
$product  = wc_get_product( $post );

switch ( WC_Aplazame::_m_or_a( $product, 'get_type', 'product_type' ) ) {
	case 'variable':
		$children_ids = $product->get_children();

		foreach ( $children_ids as $child_id ) {
			$child      = wc_get_product( $child_id );
			$articles[] = Aplazame_Aplazame_Api_BusinessModel_Article::createFromProduct( $child );
		}
		break;

	default:
		$articles[] = Aplazame_Aplazame_Api_BusinessModel_Article::createFromProduct( $product );
}

?>

<div id="aplazame_campaigns_tab" class="panel woocommerce_options_panel">
	<div class="options_group" id="aplazame_campaigns_container">
	</div>
</div>

<script>
	var campaignsContainer = document.getElementById("aplazame_campaigns_container");

	var articles = <?php echo json_encode( $articles ); ?>;

	var dateObj = new Date();
	var currentDate = dateObj.toISOString();
	var byEndDate = function (campaign) {
		return (campaign.end_date > currentDate);
	};

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
			checkbox.title = "<?php echo __( 'The campaign applies to all products from your catalogue', 'aplazame' ); ?>";
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
			async: false,
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

	function getCampaigns(page = 1) {
		apiRequest("GET", "/me/campaigns?page=" + page, null, function (payload) {
			var campaigns = payload.results;

			displayCampaigns(campaigns.filter(byEndDate));

			if (payload.cursor.after != null) {
				getCampaigns(payload.cursor.after);
			}
		});
	}

	function getCampaignsFromArticle(page = 1) {
		apiRequest("GET", "/me/campaigns?articles-mid=" + articles[0].id + "&page=" + page, null, function(payload) {
			var selectedCampaigns = payload.results;

			selectCampaigns(selectedCampaigns.filter(byEndDate));

			if (payload.cursor.after != null) {
				getCampaignsFromArticle(payload.cursor.after);
			}
		});
	}

	getCampaigns();
	getCampaignsFromArticle();
</script>
