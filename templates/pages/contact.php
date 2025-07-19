<h2>اتصل بنا</h2>
<form id="contact-form">
    <input type="text" name="name" placeholder="الاسم" required>
    <input type="email" name="email" placeholder="البريد الإلكتروني" required>
    <textarea name="message" placeholder="رسالتك" required></textarea>
    <button type="submit">إرسال</button>
</form>
<div id="form-messages"></div>

<script>
document.getElementById('contact-form').addEventListener('submit', function(e) {
    e.preventDefault();

    var form = this;
    var formData = new FormData(form);
    var formMessages = document.getElementById('form-messages');

    fetch('<?php echo SITE_URL; ?>/api/contact', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        formMessages.textContent = data.message;
        if (data.success) {
            form.reset();
        }
    })
    .catch(error => {
        formMessages.textContent = 'حدث خطأ ما. يرجى المحاولة مرة أخرى.';
    });
});
</script>
