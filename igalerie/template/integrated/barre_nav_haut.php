<?php if ($tpl->display('barre_nav_diapo') || $tpl->display('barre_nav_com')) : ?>
		<div class="barre_nav" id="barre_nav_haut">
			<div class="barre_nav_gauche"><?php $tpl->getDiaporamaLien(); ?></div>
			<div class="page_actuelle"><?php $tpl->getPageActuelle(); ?></div>

			<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>

			<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>

			<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
				<div>
					<select name="startnum" onchange="window.location.href='<?php $tpl->getUrlParameters('page'); ?>' + this.options[this.selectedIndex].value;">
						<?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?>

					</select>
				</div>
			</form>
			<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>

			<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>

		</div>
<?php endif; ?>