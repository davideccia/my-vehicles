import { useI18n } from 'vue-i18n';

export function useDateFormat() {
    const { t } = useI18n();

    function datePart(dateString: string): string {
        return dateString.split('T')[0];
    }

    function formatDate(dateString: string | null | undefined): string {
        if (!dateString) {
            return '';
        }

        const [year, month, day] = datePart(dateString).split('-');

        return t('date_format')
            .replace('dd', day)
            .replace('MM', month)
            .replace('yyyy', year);
    }

    return { formatDate };
}
