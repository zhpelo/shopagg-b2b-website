<input type="hidden" name="company_profile_combined" value="1">

<nav class="modern-tabs" aria-label="公司资料分区导航">
    <a href="#company-profile"><i class="fas fa-building mr-2 text-xs"></i>公司简介</a>
    <a href="#company-trade"><i class="fas fa-globe mr-2 text-xs"></i>贸易能力</a>
    <a href="#company-media"><i class="fas fa-images mr-2 text-xs"></i>公司展示</a>
</nav>

<div class="space-y-8">
    <section id="company-profile" class="scroll-mt-32" aria-label="公司简介">
        <?php include __DIR__ . '/company.php'; ?>
    </section>

    <section id="company-trade" class="scroll-mt-32" aria-label="贸易能力">
        <?php include __DIR__ . '/trade.php'; ?>
    </section>

    <section id="company-media" class="scroll-mt-32" aria-label="公司展示">
        <?php include __DIR__ . '/media.php'; ?>
    </section>
</div>
