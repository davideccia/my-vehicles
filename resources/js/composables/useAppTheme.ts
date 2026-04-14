import { useTheme } from 'vuetify';

export const DEFAULT_PRIMARY = '#6750A4';
export const DEFAULT_SCHEME = 'dark';

function hexToHsl(hex: string): [number, number, number] {
    const r = parseInt(hex.slice(1, 3), 16) / 255;
    const g = parseInt(hex.slice(3, 5), 16) / 255;
    const b = parseInt(hex.slice(5, 7), 16) / 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    let h = 0;
    let s = 0;
    const l = (max + min) / 2;

    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

        switch (max) {
            case r:
                h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
                break;
            case g:
                h = ((b - r) / d + 2) / 6;
                break;
            case b:
                h = ((r - g) / d + 4) / 6;
                break;
        }
    }

    return [Math.round(h * 360), Math.round(s * 100), Math.round(l * 100)];
}

function hslToHex(h: number, s: number, l: number): string {
    const sN = s / 100;
    const lN = l / 100;
    const a = sN * Math.min(lN, 1 - lN);

    const f = (n: number): string => {
        const k = (n + h / 30) % 12;
        const c = lN - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);

        return Math.round(255 * c)
            .toString(16)
            .padStart(2, '0');
    };

    return `#${f(0)}${f(8)}${f(4)}`;
}

function getSecondaryColor(primaryHex: string): string {
    const [h, s, l] = hexToHsl(primaryHex);

    return hslToHex((h + 60) % 360, s, l);
}

export function useAppTheme() {
    const theme = useTheme();

    function applyPrimaryColor(hex: string): void {
        const primary = hex || DEFAULT_PRIMARY;
        const secondary = getSecondaryColor(primary);

        for (const themeName of ['dark', 'light']) {
            if (theme.themes.value[themeName]) {
                theme.themes.value[themeName].colors.primary = primary;
                theme.themes.value[themeName].colors.secondary = secondary;
            }
        }
    }

    function applyColorScheme(scheme: string): void {
        theme.change(scheme === 'light' ? 'light' : 'dark');
    }

    return { applyPrimaryColor, applyColorScheme };
}
