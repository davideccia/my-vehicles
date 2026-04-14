export type FuelCosts = {
    labels: string[];
    datasets: Array<{
        label: string;
        backgroundColor: string;
        borderColor: string;
        data: number[];
    }>;
};
