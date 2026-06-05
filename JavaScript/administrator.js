//variabes
const rows = document.querySelectorAll('.user-row');
const userDetails = document.getElementById('userDetails');
//Switch variables
const toggleUserID = document.getElementById('toggleUserID');
const switchCheck = document.getElementById('switchCheckDefault');
const switchLabel = document.getElementById('switchLabel');

//Product Variables
const sellerRows = document.querySelectorAll('.seller-row');
const sellerDetails = document.getElementById('sellerDetails');

sellerRows.forEach(row => {
    row.addEventListener('click', function(){
        const sellerID = this.dataset.sellerid;

        fetch('getSellerProducts.php?sellerID=' + sellerID).then(response => response.json()).then(products => {
            sellerDetails.style.display = 'block';

            let html = '<h5>Products</h5><div class="container">';

            if(products.length > 0){
                products.forEach(product => {
                    html += `
                    <div class="card" style="width: 18rem;">
                        <img src="${product.ImageDir}" class="card-image-top" alt="${product.Product_Name}">
                        <div class="card-body">
                            <h5 class="card-title">${product.Product_Name}</h5>
                            <p class="card-text">R${product.Price}</p>
                        </div>
                    </div>`;
                });
            } else{
                html += '<p>This seller has no products.</p>'
            }

            html += '</div>';
            sellerDetails.innerHTML = html;
        });
    });
});

rows.forEach(row => {
    row.addEventListener('click', function(){
        document.getElementById('detailID').textContent = this.dataset.id;
        document.getElementById('detailUsername').textContent = this.dataset.username;
        document.getElementById('detailEmail').textContent = this.dataset.email;
        document.getElementById('detailStatus').textContent = this.dataset.status;
        document.getElementById('detailBuyerID').textContent = this.dataset.buyerid;
        document.getElementById('detailAddress').textContent = this.dataset.address;
        document.getElementById('detailSellerID').textContent = this.dataset.sellerid;
        document.getElementById('detailBank').textContent = this.dataset.bank;
        document.getElementById('detailBankNumber').textContent = this.dataset.banknumber;
        document.getElementById('deleteUserID').value = this.dataset.id; // send the user ID to the invisable input for deletion
        
        //switch section
        toggleUserID.value = this.dataset.id;
        switchCheck.checked = this.dataset.status === 'Active';
        switchLabel.textContent = this.dataset.status;
        
        userDetails.style.display = 'block'
    });
});