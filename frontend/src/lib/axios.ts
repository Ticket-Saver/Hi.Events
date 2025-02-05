import Axios from 'axios';

export const axios = Axios.create({
    baseURL: import.meta.env.VITE_API_URL_CLIENT,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Añadir interceptor para el token si es necesario
axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Interceptor para logs
axios.interceptors.request.use(
    (config) => {
        console.log('Request URL:', config.baseURL + config.url);
        console.log('Request Params:', config.params);
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

axios.interceptors.response.use(
    (response) => {
        console.log('Response:', response.status, response.data);
        return response;
    },
    (error) => {
        console.error('API Error:', error.response?.status, error.response?.data);
        return Promise.reject(error);
    }
); 