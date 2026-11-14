import { apiFetch } from "../api/api.js";

const BASE_URL = "/esportes";

export const esporteService = {
    listar() {
        return apiFetch(BASE_URL);
    }
};