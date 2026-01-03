export const getStatusLabel = (value) => {
    return {
        '0': 'Okay',
        '1': 'Fail',
        '2': 'Error',
    }[value] || 'Unknown';
};

export const getStatusTextClasses = (value) => {
    return {
        '0': 'text-green-500',
        '1': 'text-red-500',
        '2': 'text-orange-500',
    }[value] || 'Unknown';
};

export const getStatusClasses = (value) => {
    return {
        '0': 'bg-green-100 border-green-200',
        '1': 'bg-red-100 border-red-200',
        '2': 'bg-orange-100 border-orange-200',
    }[value] || '';
};
