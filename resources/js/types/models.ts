export type Vehicle = {
    id: string;
    plate_number: string;
    brand: string;
    model: string;
    year: number;
    purchase_date: string | null;
    full_name: string;
    created_at: string;
    updated_at: string;
};

export type VehicleRefuel = {
    id: string;
    vehicle_id: string;
    vehicle?: Vehicle;
    date: string;
    total_price: number;
    unit_price: number;
    liters: number;
    odometer: number;
    created_at: string;
    updated_at: string;
};

export type VehicleServiceType = {
    id: string;
    icon: string;
    label: string;
    created_at: string;
    updated_at: string;
};

export type VehicleService = {
    id: string;
    vehicle_id: string;
    vehicle_service_type_id: string;
    vehicle_service_reminder_id: string | null;
    vehicle?: Vehicle;
    vehicle_service_type?: VehicleServiceType;
    vehicle_service_reminder?: VehicleServiceReminder;
    date: string;
    total_paid: number;
    odometer: number;
    location: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
};

export type VehicleServiceReminder = {
    id: string;
    vehicle_id: string;
    vehicle_service_type_id: string;
    vehicle?: Vehicle;
    vehicle_service_type?: VehicleServiceType;
    latest_vehicle_service?: VehicleService | null;
    every: number;
    current_vehicle_odometer: number;
    last_vehicle_service_odometer: number;
    overdue_odometer_diff: number;
    is_overdue: boolean;
    created_at: string;
    updated_at: string;
};
