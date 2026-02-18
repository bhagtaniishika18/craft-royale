< !--Global Receipt Download Handler-- >
    <script>
        window.downloadReceipt = function(orderId) {
    if (window.isReceiptGenerating) return;
        window.isReceiptGenerating = true;

        if (typeof Swal === 'undefined') {
            window.open('download_receipt.php?id=' + orderId, '_blank');
        window.isReceiptGenerating = false;
        return;
    }

        // Small, Minimalist Loading Box
        Swal.fire({
            title: 'Preparing Bill',
        html: `
        <div class="mini-premium-loader">
            <div class="mini-premium-spinner"></div>
            <div class="loader-text-content">
                <p class="main-msg">Generating your professional bill</p>
                <p class="sub-msg">This will take just a few seconds...</p>
            </div>
        </div>
        `,
        width: '320px',
        padding: '1.5rem',
        showConfirmButton: false,
        showCancelButton: false,
        showCloseButton: true,
        allowOutsideClick: true,
        allowEscapeKey: true,
        customClass: {
            popup: 'premium-mini-popup',
        title: 'premium-mini-title'
        },
        didClose: () => {
            window.isReceiptGenerating = false;
        const temp = document.getElementById('temp-receipt-dl-container');
        if (temp && temp.parentNode) temp.parentNode.removeChild(temp);
        },
        didOpen: () => {
            fetch('download_receipt.php?id=' + orderId + '&html_only=1')
                .then(response => response.text())
                .then(html => {
                    const container = document.createElement('div');
                    container.id = 'temp-receipt-dl-container';
                    container.style.position = 'fixed';
                    container.style.left = '-10000px';
                    container.style.top = '0';
                    container.style.width = '800px';
                    container.style.background = '#fff';
                    container.innerHTML = html;
                    document.body.appendChild(container);

                    const images = container.getElementsByTagName('img');
                    let imagesLoaded = 0;
                    let captureCalled = false;

                    const captureAndDownload = () => {
                        if (captureCalled) return;
                        captureCalled = true;

                        html2canvas(container, {
                            scale: 1.5,
                            useCORS: true,
                            logging: false,
                            backgroundColor: '#ffffff',
                            windowWidth: 800
                        }).then(canvas => {
                            const { jsPDF } = window.jspdf;
                            const pdf = new jsPDF('p', 'mm', 'a4');
                            const imgData = canvas.toDataURL('image/png', 0.8);
                            const pdfWidth = pdf.internal.pageSize.getWidth();
                            const ratio = (pdfWidth - 20) / canvas.width;

                            pdf.addImage(imgData, 'PNG', 10, 10, canvas.width * ratio, canvas.height * ratio);

                            // 1. Trigger Download
                            pdf.save('Receipt_Order_' + orderId + '.pdf');

                            // 2. Transition to Success State
                            const popup = Swal.getPopup();
                            popup.classList.add('premium-success-state');
                            Swal.update({ title: 'Bill Generated' });

                            const loaderWrap = popup.querySelector('.mini-premium-loader');
                            loaderWrap.innerHTML = `
                                <div class="success-checkmark-ani">
                                    <div class="check-icon">
                                        <span class="icon-line long"></span>
                                        <span class="icon-line tip"></span>
                                        <div class="icon-circle"></div>
                                        <div class="icon-fix"></div>
                                    </div>
                                </div>
                                <div class="loader-text-content">
                                    <p class="main-msg success-text">Receipt Ready!</p>
                                    <p class="sub-msg">Your document has been downloaded.</p>
                                </div>
                            `;

                            // 3. Close after showing success for 2 seconds
                            setTimeout(() => {
                                Swal.close();
                                if (container.parentNode) document.body.removeChild(container);
                                window.isReceiptGenerating = false;
                            }, 2000);

                        }).catch(err => {
                            console.error('PDF Error:', err);
                            if (container.parentNode) document.body.removeChild(container);
                            Swal.close();
                            window.isReceiptGenerating = false;
                            window.open('download_receipt.php?id=' + orderId, '_blank');
                        });
                    };

                    if (images.length === 0) {
                        captureAndDownload();
                    } else {
                        Array.from(images).forEach(img => {
                            const onImgFinish = () => {
                                imagesLoaded++;
                                if (imagesLoaded >= images.length) captureAndDownload();
                            };
                            if (img.complete) onImgFinish();
                            else img.onload = img.onerror = onImgFinish;
                        });
                        // Max 3 seconds wait for images
                        setTimeout(() => { if (!captureCalled) captureAndDownload(); }, 3000);
                    }
                })
                .catch(err => {
                    console.error('Fetch Error:', err);
                    Swal.close();
                    window.isReceiptGenerating = false;
                    window.open('download_receipt.php?id=' + orderId, '_blank');
                });
        }
    });
};
    </script>