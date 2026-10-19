<script>
    document.getElementById('copy-orderer').addEventListener('click', () => {
        for (const field of ['name', 'postal_code', 'address', 'phone']) {
            document.getElementById(`shipping_${field}`).value =
                document.getElementById(`orderer_${field}`).value;
        }
    });
</script>
