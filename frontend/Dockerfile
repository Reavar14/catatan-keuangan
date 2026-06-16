FROM node:20-alpine AS build-stage

WORKDIR /app

# Copy package files dan install
COPY package*.json ./
RUN npm install

# Copy seluruh source code & build production
COPY . .
RUN npm run build

# Stage production menggunakan Nginx untuk serve hasil build static
FROM nginx:stable-alpine AS production-stage
COPY --from=build-stage /app/dist /usr/share/nginx/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]