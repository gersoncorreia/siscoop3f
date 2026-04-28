export const maskDirective = {
    mounted(el, binding) {
        const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
        if (!input) return;

        const updateValue = () => {
            const oldValue = input.value;
            let value = input.value.replace(/\D/g, '');
            const type = binding.value;

            if (type === 'cpf') {
                value = value.slice(0, 11);
                input.value = value
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})/, '$1-$2');
            } else if (type === 'cep') {
                value = value.slice(0, 8);
                input.value = value.replace(/(\d{5})(\d)/, '$1-$2');
            } else if (type === 'phone') {
                value = value.slice(0, 11);
                if (value.length > 10) {
                    input.value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length > 2) {
                    input.value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                } else if (value.length > 0) {
                    input.value = value.replace(/(\d{0,2})/, '($1');
                }
            } else if (type === 'currency') {
                if (value === '') value = '0';
                let floatValue = (parseInt(value) / 100).toFixed(2);
                input.value = 'R$ ' + floatValue.replace('.', ',')
                    .replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            }

            // Important: update the internal value or dispatch events only if changed
            if (input.value !== oldValue) {
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };

        input.addEventListener('input', updateValue);
        // Apply initial mask if value exists
        if (input.value) updateValue();
    }
};
