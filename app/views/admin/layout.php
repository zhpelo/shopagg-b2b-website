<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
    <style>body{background:#f5f7fb}.admin-card{box-shadow:0 10px 30px rgba(15,23,42,0.08)}</style>
</head>
<body>
<?php if ($showNav ?? true): ?>
    <nav class="navbar is-white is-spaced">
        <div class="container">
            <div class="navbar-brand"><a class="navbar-item" href="/admin"><strong> 管理后台 </strong></a></div>
            <div class="navbar-menu is-active">
                <div class="navbar-start">
                    <a class="navbar-item" href="/admin">仪表盘</a>
                    <a class="navbar-item" href="/admin/products">产品</a>
                    <a class="navbar-item" href="/admin/categories">产品分类</a>
                    <a class="navbar-item" href="/admin/cases">案例</a>
                    <a class="navbar-item" href="/admin/posts">博客</a>
                    <a class="navbar-item" href="/admin/messages">留言</a>
                    <a class="navbar-item" href="/admin/inquiries">询单</a>
                    <a class="navbar-item" href="/admin/settings">设置</a>
                </div>
                <div class="navbar-end">
                    <div class="navbar-item"><a class="button is-light" href="/admin/logout">退出登录</a></div>
                </div>
            </div>
        </div>
    </nav>
<?php endif; ?>

<div class="admin-page" style="min-height: 700px;">
    <section class="section">
        <div class="container">
            <?= $content ?>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Quill Editor
    const editor = document.getElementById("quill-editor");
    const input = document.getElementById("content-input");
    if (editor && input) {
        const quill = new Quill(editor, {
            theme: "snow",
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ["bold", "italic", "underline", "strike"],
                    [{ list: "ordered" }, { list: "bullet" }],
                    ["link", "image"],
                    ["clean"]
                ]
            }
        });
        quill.root.innerHTML = input.value || "";
        const form = input.closest("form");
        form.addEventListener("submit", function () {
            input.value = quill.root.innerHTML;
        });

        // AJAX Image for Quill
        const toolbar = quill.getModule("toolbar");
        toolbar.addHandler("image", function () {
            const inputFile = document.createElement("input");
            inputFile.type = "file";
            inputFile.accept = "image/*";
            inputFile.click();
            inputFile.addEventListener("change", function () {
                const file = inputFile.files[0];
                if (!file) return;
                const formData = new FormData();
                formData.append("image", file);
                formData.append("csrf", "<?= csrf_token() ?>");
                fetch("/admin/upload-image", { method: "POST", body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.url) {
                            const range = quill.getSelection(true);
                            quill.insertEmbed(range ? range.index : 0, "image", data.url);
                        } else {
                            alert(data.error || "上传失败");
                        }
                    });
            });
        });
    }

    // 2. Product Image Preview
    const imageInput = document.querySelector("input[name='images[]']");
    const preview = document.getElementById("product-image-preview");
    if (imageInput && preview) {
        imageInput.addEventListener("change", function () {
            preview.innerHTML = "";
            Array.from(imageInput.files).slice(0, 6).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const div = document.createElement("div");
                    div.className = "column is-3";
                    div.innerHTML = `<figure class="image"><img src="${e.target.result}"></figure>`;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // 3. Price Tiers
    const addTierBtn = document.getElementById("add-price-tier");
    const tierWrap = document.getElementById("price-tier-wrap");
    if (addTierBtn && tierWrap) {
        addTierBtn.addEventListener("click", function () {
            const row = document.createElement("div");
            row.className = "columns price-tier-row";
            row.innerHTML = `
                <div class="column"><div class="field"><label class="label is-size-7">最小数量</label><div class="control"><input class="input" name="price_min[]" type="number" min="1" required></div></div></div>
                <div class="column"><div class="field"><label class="label is-size-7">最大数量</label><div class="control"><input class="input" name="price_max[]" type="number" min="1" placeholder="可空"></div></div></div>
                <div class="column"><div class="field"><label class="label is-size-7">单价</label><div class="control"><input class="input" name="price_value[]" type="number" min="0" step="0.01" required></div></div></div>
                <div class="column"><div class="field"><label class="label is-size-7">货币</label><div class="control"><input class="input" name="price_currency[]" value="USD" required></div></div></div>
                <div class="column is-narrow"><div class="field"><label class="label is-size-7">操作</label><div class="control"><button type="button" class="button is-light remove-price-tier">删除</button></div></div></div>
            `;
            tierWrap.appendChild(row);
        });
        tierWrap.addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-price-tier")) {
                e.target.closest(".price-tier-row").remove();
            }
        });
    }
});
</script>
</body>
</html>
