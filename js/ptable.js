$.fn.ptable = function(targets) {
	var prefix = "#ptable_", need_worker = false, updater = false, settings = Array();
	
	if (targets == undefined)
		targets = ".ptable";

	$(targets).each(function() {
		var ptable = $(this).prop("id");
		
		settings[ptable] = {
			target: $(this).prop("id"),
			data: $(this).data(),
			mode: "init",
			url: "ptable.php",
			page: 1,
			search: "",
			search_from: 3,
			autoupdate: 0
		}

		// tabeli kuvamine

		update(ptable);

		// valikutekasti kuvamine/peitmine
			
		$("#" + settings[ptable].target).on("click", ".pref_btn", function() {
			$("#" + $(this).prop("id") + "box").toggle();

			autoupdate_check();
		});
		
		// klaviatuuriga tabeli juhtimine
		
		$(document).on("keyup", function(e) {
			// TODO: "keydown" actioniga pgup/pgdown ja nooltega ka üles/alla liikumine tabelis (ainult kui tabel on fookuses)
			
			//e.preventDefault();
			e.stopImmediatePropagation();
			
			// kui mingi tabel omab fookust (mouseover) ja navigeerimine on lubatud (vasakule-paremale nooled)
			
			$(".ptable").each(function() {
				var ct = $(this).prop("id");
				
				if ($(this).is(":hover") && $(prefix + settings[ct].target).data("navigation")) {
					if (e.keyCode == 37 && settings[ct].page > 1) { // nool vasakule
						settings[ct].page--;
						update(ct);
					}
					else if (e.keyCode == 39 && settings[ct].page < $(prefix + settings[ct].target).data("pages")) { // nool paremale
						settings[ct].page++;
						update(ct);
					}					
				}
			});
		});

		// triggerite käivitamine (ainult lingid)
		// ('data'-välja puhul välise skripti asi kontrollida, mis juhtub kui real klikitakse)

		$("#" + settings[ptable].target).on("click", ".trigger", function(e) {
			e.stopImmediatePropagation();							// ära luba mitut trigerit, ainult kõige esimest
				
			if ($(this).data("link")) {
				if ($(this).data("ext"))
					window.open($(this).data("link"), "_blank");	// avab uues aknas
				else
					location.href = $(this).data("link");			// avab samas aknas
			}
			else {
				trigger($(this).data());							// tee midagi selle saadetava infoga
			}
		});
			
		// tabeli kirjete arvu muutmine ja vastavale uuele leheküljele viimine

		$("#" + settings[ptable].target).on("change", prefix + settings[ptable].target + "_pagesize", function() {
			var old_pagesize = $(prefix + settings[ptable].target).data("page_size");
			var old_page = $(prefix + settings[ptable].target).data("page") - 1;
			var landing_page = 1;
				
			// kui uus või vana valik pole "kõik read", siis arvuta millisele lehele oleks sobiv kasutaja visata
				
			if ($(this).val() != '*' && old_pagesize != '*')
				landing_page = Math.floor((old_page * old_pagesize) / $(this).val()) + 1;
				
			settings[ptable].page = landing_page;
			settings[ptable].page_size = $(this).val();

			update(ptable);
		});
			
		// otsingukasti sisu puhul enter triggerdab otsingunuppu

		$("#" + settings[ptable].target).on("change", prefix + settings[ptable].target + "_search", function() {
			$(prefix + settings[ptable].target + "_commit_search").trigger("click");
		});
			
		// automaatne otsing (kui lubatud)

		$("#" + settings[ptable].target).on("keyup", prefix + settings[ptable].target + "_search", function(e) {
			if (e.keyCode < 46 && e.keyCode != 32 && e.keyCode != 8) // kui ei ole reaalsed tähemärgid ja ei ole del, backspace, tühik
				return false;

			if ($(prefix + settings[ptable].target).data("autosearch")) {
				settings[ptable].search = $(prefix + settings[ptable].target + "_search").val();

				if (settings[ptable].search.length < settings[ptable].search_from) {
					if (e.keyCode == 8 || e.keyCode == 46) // kui vajutatakse backspace või del
						settings[ptable].search = "";
					else
						return false;
				}

				update(ptable);
			}
		});
		
		// et väljaotsingukastil endal klikkimine ei vallandaks sorteerimistriggerit
		
		$("#" + settings[ptable].target).on("click", ".field_search", function(e) {
			e.stopImmediatePropagation();
		});

		// väljaotsingu kasti kuvamine

		/*$("#" + settings[ptable].target).on("click", ".field_search_btn", function(e) {
			e.stopImmediatePropagation();
			
			$("#
			$("#" + $(this).prop("id") + "_input").toggle();
		});*/
		
		// tabeli uuendamine

		$("#" + settings[ptable].target).on("click", ".nav, .order, .search_btn", function() {
			if ($(this).hasClass("order")) { // tabeli välja peal klikkimise puhul sorteeri selle järgi
				if (settings[ptable].order == undefined) {
					settings[ptable].order = $(prefix + settings[ptable].target).data("order");
					settings[ptable].way = $(prefix + settings[ptable].target).data("way");
				}

				if (settings[ptable].order == $(this).data("field")) { // teistkordsel klikkimisel sama välja peal, muuda järjestamine vastupidiseks
					if (settings[ptable].way == "asc")
						settings[ptable].way = "desc";
					else
						settings[ptable].way = "asc";
				}
				else {						
					settings[ptable].way = "asc"; // uue välja klikkimisel on järjekord kasvav
				}

				settings[ptable].order = $(this).data("field");
			}
			else if ($(this).hasClass("search_btn")) {
				settings[ptable].search = $(prefix + settings[ptable].target + "_search").val();
			}
			else { // ära navigeeri, kui see on keelatud või kui leht juba on aktiivne
				if ($(this).hasClass("denied") || $(this).hasClass("selected"))
					return false;
				
				settings[ptable].page = $(this).data("page");
			}

			update(ptable);
		});
	});
	
	// uuenda tabelit
	
	function update(ptable) {
		var timestamp = Math.floor(Date.now() / 1000);
		
		// kui tabel pole hetkel nähtav, siis ei uuenda ka
		
		if (settings[ptable].mode == "update" && $("#" + settings[ptable].target).is(":hidden"))
			return false;
		
		// uuenda tabelit

		$.ajax({ url: settings[ptable].url, data: { ptable: settings[ptable] } }).done(function(content) {
			if (settings[ptable].mode == "init")
				$("#" + settings[ptable].target).html(content);
			else
				$(prefix + settings[ptable].target + "_container").html(content);
			
			settings[ptable].page = $(prefix + settings[ptable].target).data("page");
			settings[ptable].autoupdate = $(prefix + settings[ptable].target).data("autoupdate");
			settings[ptable].last_update = timestamp;
			settings[ptable].mode = "update";
			
			autoupdate_check();
		});
	}
	
	function autoupdate_check() {
		need_worker = false;
		
		// kas mõnda tabelit on vaja automaatselt uuendada?

		for (pt in settings)
			if ($(prefix + settings[pt].target).data("autoupdate"))
				need_worker = true;

		// kas on vaja seada uuenduse intervall või hoopis panna worker seisma, kuna ükski tabel ei vaja enam seda?

		if (need_worker && !updater)
			updater = setInterval(worker, 1000);
		else if (!need_worker && updater)
			clearInterval(updater);
	}
	
	// tee midagi triggeriga rea või välja peal klikkimise peale (mitte lingi puhul siis)
	
	function trigger(data) {
		var what = "";
		
		$.each(data, function(i, field) {
			what += " [" + field + "]";
		});
		
		alert(what);
	}

	// uuenda tabelit automaatselt, kui on autoupdate seatud tabelile ja eelmisest updatest on määratud aeg mööda läinud

	function worker() {
		var timestamp = Math.floor(Date.now() / 1000);

		// uuenda vajalikku tabelit, kui vastav aeg on mööda läinud viimasest uuendusest
		
		for (pt in settings)
			if ($(prefix + settings[pt].target).data("autoupdate") && timestamp > (settings[pt].last_update + $(prefix + settings[pt].target).data("autoupdate"))) {
				console.log(pt + ":" + $(prefix + settings[pt].target).data("autoupdate"));
				update(pt);
			}
	}

	// keeleuuendus (ainult demo jaoks)
	
	$(".lang").click(function() {
		if ($(this).hasClass("current"))
			return false;
		
		location.href = "/ptable/?lang=" + $(this).data("lang");
	});
	
	// autoupdate checkboxi ja valikukasti aktiveerimine
	
	$(".ptable").on("click", ".autoupdate_check", function() {
		var tbl = $(this).data("table");
		
		if ($(this).hasClass("off")) {
			var upd = parseInt($(prefix + tbl + "_autoupdate_select").val())

			$(prefix + tbl + "_autoupdate_off").hide();
			$(prefix + tbl + "_autoupdate_on").show();
			$(prefix +tbl + "_autoupdate_select").prop("disabled", false);

			settings[tbl].autoupdate = upd;
			$(prefix + tbl).data("autoupdate", upd);
		}
		else {
			$(prefix + tbl + "_autoupdate_on").hide();
			$(prefix + tbl + "_autoupdate_off").show();
			$(prefix + tbl + "_autoupdate_select").prop("disabled", "disabled");

			settings[tbl].autoupdate = 0;
			$(prefix + tbl).data("autoupdate", 0);
		}

		autoupdate_check();
	});
	
	// kui muudetakse autoupdate aega

	$(".ptable").on("change", ".autoupdate_select", function() {
		var tbl = $(this).data("table");
		var upd = parseInt($(this).val())
		
		settings[tbl].autoupdate = upd;
		$(prefix + tbl).data("autoupdate", upd);
	});
};
