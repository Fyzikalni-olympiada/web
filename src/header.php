<?php
/**
 * Hlavička webu (logo + hlavní menu). Očekává $menu_aktivni — pathname
 * aktuální stránky, případně href externí položky (Osmo).
 * Build z ní generuje i dist/header.html pro Osmo (viz build.php):
 * s $hlavicka_osmo se místo ročníku vykreslí badge odevzdávacího systému.
 */
?>
    <div id="header" class="row">
		<div class="col-xs-6 col-sm-5" id="logo">
			<a href="/"><img src="/pic/logo-fo.svg" alt="" /> </a>
			<a href="/"><span>FYZIKÁLNÍ OLYMPIÁDA</span></a>
<?php if (!empty($hlavicka_osmo)) : ?>
			<span class="rocnik osmo-badge"><a href="https://osmo.fyzikalniolympiada.cz/"><?php
				echo str_replace('<svg ', '<svg class="ikona-vir" ', trim(file_get_contents(__DIR__ . '/../assets/pic/menu/vir.svg')));
			?><b>Osmo</b></a> <span class="popisek">odevzdávací systém</span></span>
<?php else : ?>
			<span class="rocnik"><b><?php echo AKTUALNI_ROCNIK ?>.&nbsp;ročník</b> &middot; <?php echo AKTUALNI_ROK ?></span>
<?php endif; ?>
			<button type="button" id="menu-tlacitko" aria-expanded="false"><span class="linky" aria-hidden="true"><span></span><span></span><span></span></span>Menu</button>
		</div>

		<div class="col-xs-6 col-sm-7 navigation" id="main-nav">
			<?php echo menu($menu_aktivni); ?>
			<form class="search-mobil" role="search" action="/vyhledavani">
			<label class="sr-only" for="q-mobil">Vyhledávání</label>
			  <div class="input-group">
				<input class="form-control" placeholder="Vyhledávání" type="text" name="q" id="q-mobil" autocomplete="off" />
				<span class="input-group-btn">
				<button class="btn btn-default" type="submit">
					<svg class="ikona-lupa" viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.3 15.3 21 21"/></svg>
				</button>
				</span>
			  </div>
			</form>
		</div>

		<div class="clearfix"></div>
    </div>
